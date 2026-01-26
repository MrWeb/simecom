<?php

namespace App\Jobs;

use App\Models\OfferCode;
use App\Models\SkippedImport;
use App\Models\VideoCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProcessExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        protected string $filePath,
        protected bool $skipSend = false
    ) {}

    public function handle(): void
    {
        // Supporta sia path assoluti che relativi a storage/app
        $fullPath = str_starts_with($this->filePath, '/')
            ? $this->filePath
            : storage_path("app/{$this->filePath}");

        if (! file_exists($fullPath)) {
            Log::error('Excel file not found', ['path' => $fullPath]);
            return;
        }

        // Leggi il file (supporta CSV con ; e Excel)
        $rows = $this->readFile($fullPath);

        // Skip header row
        $header = array_shift($rows);
        $headerMap = array_flip($header);

        $processed = 0;
        $reused = 0;

        $sourceFileName = basename($this->filePath);

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 perché: +1 per header, +1 perché gli array partono da 0

            try {
                $campaign = $this->processRow($row, $headerMap, $header, $sourceFileName, $rowNumber);

                if ($campaign) {
                    $processed++;

                    // Check if video already exists
                    $existing = $campaign->findExistingVideo();

                    if ($existing) {
                        $campaign->reuseVideoFrom($existing);
                        $reused++;
                        // Video ready, dispatch notification job (unless skipped)
                        if (!$this->skipSend) {
                            $this->dispatchNotificationJob($campaign);
                        }
                    } else {
                        // Need to generate new video
                        GenerateVideoJob::dispatch($campaign, $this->skipSend);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to process Excel row', [
                    'row' => $row,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Excel processing completed', [
            'file' => $this->filePath,
            'processed' => $processed,
            'reused' => $reused,
        ]);
    }

    protected function processRow(array $row, array $headerMap, array $header, string $sourceFile, int $rowNumber): ?VideoCampaign
    {
        // Mappatura colonne dal file Simecom
        $email = $row[$headerMap['MAIL'] ?? $headerMap['mail'] ?? 0] ?? null;
        $nome = $row[$headerMap['nom'] ?? $headerMap['NOM'] ?? 1] ?? '';
        $cognome = $row[$headerMap['cog'] ?? $headerMap['COG'] ?? 2] ?? '';
        $codiceOfferta = $row[$headerMap['CODOF'] ?? $headerMap['codof'] ?? 3] ?? '';
        $sesso = $row[$headerMap['SEX'] ?? $headerMap['sex'] ?? 4] ?? 'M';

        // Parsing telefono - supporta varie varianti di nome colonna
        $phone = $this->extractPhone($row, $headerMap);

        $customerName = trim("{$nome} {$cognome}");

        // Se cliente, email, telefono e codice offerta sono tutti vuoti, ignora la riga
        if (empty($email) && empty($phone) && empty($customerName) && empty($codiceOfferta)) {
            return null;
        }

        // Costruisci row_data associativo per riferimento
        $rowData = array_combine($header, $row);

        // Se mancano sia email che telefono, scarta
        if (empty($email) && empty($phone)) {
            SkippedImport::create([
                'source_file' => $sourceFile,
                'row_number' => $rowNumber,
                'row_data' => $rowData,
                'error_type' => 'missing_contact',
                'offer_code' => $codiceOfferta ?: null,
                'email' => null,
                'phone' => null,
                'customer_name' => $customerName ?: null,
            ]);
            return null;
        }

        // Cerca il codice offerta nella tabella
        $offerCode = OfferCode::findByCode($codiceOfferta);

        if (!$offerCode) {
            Log::warning('Codice offerta non trovato', ['codice' => $codiceOfferta]);
            SkippedImport::create([
                'source_file' => $sourceFile,
                'row_number' => $rowNumber,
                'row_data' => $rowData,
                'error_type' => 'missing_offer_code',
                'offer_code' => $codiceOfferta ?: null,
                'email' => $email ?: null,
                'phone' => $phone ?: null,
                'customer_name' => $customerName ?: null,
            ]);
            return null;
        }

        $tipoFinale = ($sesso === 'F') ? 2 : 1;
        $fatturaWeb = strtoupper($row[$headerMap['fatturaWEB'] ?? $headerMap['fatturaweb'] ?? $headerMap['FATTURAWEB'] ?? 5] ?? 'NO');

        // Costruisci la combinazione video
        $combination = $this->buildVideoCombination($offerCode->video_segment, $tipoFinale, $fatturaWeb === 'SI');

        return VideoCampaign::create([
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'customer_name' => $customerName,
            'video_combination' => $combination,
            'video_type' => $offerCode->type,
            'offer_code' => $offerCode->code,
            'offer_name' => $offerCode->offer_name,
        ]);
    }

    /**
     * Estrae il numero di telefono dalla riga, supportando varie varianti di nome colonna.
     */
    protected function extractPhone(array $row, array $headerMap): ?string
    {
        // Lista di possibili nomi colonna per il telefono
        $phoneColumns = ['CEL', 'cel', 'TEL', 'tel', 'TELEFONO', 'telefono', 'PHONE', 'phone', 'CELLULARE', 'cellulare', 'CELL', 'cell'];

        foreach ($phoneColumns as $col) {
            if (isset($headerMap[$col]) && !empty($row[$headerMap[$col]])) {
                return trim($row[$headerMap[$col]]);
            }
        }

        return null;
    }

    /**
     * Dispatcha il job di notifica appropriato (email o SMS).
     */
    protected function dispatchNotificationJob(VideoCampaign $campaign): void
    {
        $channel = $campaign->getPreferredChannel();

        if ($channel === 'email') {
            SendCampaignEmailJob::dispatch($campaign);
        } elseif ($channel === 'sms') {
            SendCampaignSmsJob::dispatch($campaign);
        }
    }

    protected function buildVideoCombination(?string $videoSegment, int $tipoFinale, bool $hasFatturaWeb = false): array
    {
        $combination = [
            'benvenuto',
            $videoSegment ?? '__DYNAMIC_OFFER__',
        ];

        if (!$hasFatturaWeb) {
            $combination[] = 'bolletta-digitale';
        }

        $combination[] = 'porta-un-amico';
        $combination[] = 'prodotti';

        if (!$hasFatturaWeb) {
            $combination[] = 'bolletta-digitale-2';
        }else{
            $combination[] = 'fine-1'; // . $tipoFinale
        }

        return $combination;
    }

    protected function readFile(string $fullPath): array
    {
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Per CSV, prova a rilevare il separatore
        if ($extension === 'csv') {
            return $this->readCsv($fullPath);
        }

        // Per Excel usa Maatwebsite
        return Excel::toArray(null, $fullPath)[0] ?? [];
    }

    protected function readCsv(string $fullPath): array
    {
        // Leggi il contenuto e normalizza i line endings
        $content = file_get_contents($fullPath);

        if ($content === false) {
            return [];
        }

        // Normalizza line endings: \r\n -> \n, \r -> \n
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Rileva il separatore dalla prima riga
        $firstLine = strtok($content, "\n");
        $separator = str_contains($firstLine, ';') ? ';' : ',';

        // Parsa le righe
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
