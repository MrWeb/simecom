<?php

namespace App\Jobs;

use App\Models\OfferCode;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportOfferCodesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        protected string $filePath,
        protected int $userId,
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::error('ImportOfferCodesJob: utente non trovato', ['userId' => $this->userId]);
            return;
        }

        $fullPath = str_starts_with($this->filePath, '/')
            ? $this->filePath
            : Storage::disk('local')->path($this->filePath);

        if (! file_exists($fullPath)) {
            Log::error('ImportOfferCodesJob: file non trovato', ['path' => $fullPath]);
            Notification::make()
                ->title('Importazione codici offerta fallita')
                ->body('File non trovato.')
                ->danger()
                ->sendToDatabase($user);
            return;
        }

        $rows = $this->readFile($fullPath);

        // Trova l'offset della prima colonna non-vuota (alcune sheet hanno colonna A vuota)
        $colOffset = 0;
        foreach ($rows as $row) {
            foreach ($row as $colIndex => $cell) {
                if (trim((string) ($cell ?? '')) !== '') {
                    $colOffset = $colIndex;
                    break 2;
                }
            }
        }

        // Skippa tutte le righe vuote iniziali + la prima riga con dati (header)
        $headerFound = false;
        $dataRows = [];
        foreach ($rows as $index => $row) {
            $firstCell = trim((string) ($row[$colOffset] ?? ''));
            if (! $headerFound) {
                if ($firstCell !== '') {
                    $headerFound = true; // Questa è l'header, la skippiamo
                }
                continue;
            }
            $dataRows[$index] = $row;
        }

        $validRows = [];
        $errors = [];

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 1;

            // Colonne posizionali: col[0]=mercato (EE/GAS), col[1]=codice, col[2]=nome offerta
            $mercato = strtoupper(trim((string) ($row[$colOffset] ?? '')));
            $codice = trim((string) ($row[$colOffset + 1] ?? ''));
            $nomeOfferta = trim((string) ($row[$colOffset + 2] ?? ''));

            // Mappa mercato → tipo: EE = luce, GAS = gas
            $tipo = match ($mercato) {
                'EE' => 'luce',
                'GAS' => 'gas',
                default => '',
            };

            // Riga completamente vuota → skip silenzioso
            if ($codice === '' && $nomeOfferta === '' && $tipo === '') {
                continue;
            }

            if ($codice === '') {
                $errors[] = "Riga {$rowNumber}: codice mancante";
                continue;
            }

            if (! in_array($tipo, ['luce', 'gas'])) {
                $errors[] = "Riga {$rowNumber}: mercato non valido \"{$mercato}\" (deve essere EE o GAS)";
                continue;
            }

            $validRows[] = [
                'code' => $codice,
                'offer_name' => $nomeOfferta,
                'type' => $tipo,
                'active' => true,
            ];
        }

        if (empty($validRows)) {
            $body = "Nessuna riga valida trovata. " . count($errors) . " righe con errori.";
            if (! empty($errors)) {
                $body .= "\n" . implode("\n", array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $body .= "\n... e altri " . (count($errors) - 10) . " errori";
                }
            }

            Notification::make()
                ->title('Importazione codici offerta fallita')
                ->body($body)
                ->danger()
                ->sendToDatabase($user);

            Log::warning('ImportOfferCodesJob: nessuna riga valida', ['errors' => $errors]);
            return;
        }

        // Delete e insert solo se ci sono righe valide
        OfferCode::query()->delete();

        foreach (array_chunk($validRows, 500) as $chunk) {
            OfferCode::insert($chunk);
        }

        $imported = count($validRows);
        $errored = count($errors);

        $body = "{$imported} codici importati.";
        if ($errored > 0) {
            $body .= " {$errored} righe non importate per errori:";
            $body .= "\n" . implode("\n", array_slice($errors, 0, 10));
            if ($errored > 10) {
                $body .= "\n... e altri " . ($errored - 10) . " errori";
            }
        }

        Notification::make()
            ->title('Importazione codici offerta completata')
            ->body($body)
            ->success()
            ->sendToDatabase($user);

        // Cleanup del file temporaneo
        @unlink($fullPath);

        Log::info('ImportOfferCodesJob completato', [
            'imported' => $imported,
            'errors' => $errored,
        ]);
    }

    protected function readFile(string $fullPath): array
    {
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->readCsv($fullPath);
        }

        return Excel::toArray(null, $fullPath)[0] ?? [];
    }

    protected function readCsv(string $fullPath): array
    {
        $content = file_get_contents($fullPath);

        if ($content === false) {
            return [];
        }

        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $firstLine = strtok($content, "\n");
        $separator = str_contains($firstLine, ';') ? ';' : ',';

        $rows = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $rows[] = str_getcsv($line, $separator);
        }

        return $rows;
    }
}
