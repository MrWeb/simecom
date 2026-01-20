<?php

namespace App\Jobs;

use App\Models\OfferCode;
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
        protected bool $skipEmail = false
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

        foreach ($rows as $row) {
            try {
                $campaign = $this->processRow($row, $headerMap);

                if ($campaign) {
                    $processed++;

                    // Check if video already exists
                    $existing = $campaign->findExistingVideo();

                    if ($existing) {
                        $campaign->reuseVideoFrom($existing);
                        $reused++;
                        // Video ready, dispatch email job (unless skipped)
                        if (!$this->skipEmail) {
                            SendCampaignEmailJob::dispatch($campaign);
                        }
                    } else {
                        // Need to generate new video
                        GenerateVideoJob::dispatch($campaign, $this->skipEmail);
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

    protected function processRow(array $row, array $headerMap): ?VideoCampaign
    {
        // Mappatura colonne dal file Simecom
        $email = $row[$headerMap['MAIL'] ?? $headerMap['mail'] ?? 0] ?? null;
        $nome = $row[$headerMap['nom'] ?? $headerMap['NOM'] ?? 1] ?? '';
        $cognome = $row[$headerMap['cog'] ?? $headerMap['COG'] ?? 2] ?? '';
        $codiceOfferta = $row[$headerMap['CODOF'] ?? $headerMap['codof'] ?? 3] ?? '';
        $sesso = $row[$headerMap['SEX'] ?? $headerMap['sex'] ?? 4] ?? 'M';

        if (empty($email)) {
            return null;
        }

        $customerName = trim("{$nome} {$cognome}");

        // Cerca il codice offerta nella tabella
        $offerCode = OfferCode::findByCode($codiceOfferta);

        if (!$offerCode) {
            Log::warning('Codice offerta non trovato', ['codice' => $codiceOfferta]);
            return null;
        }

        $tipoFinale = ($sesso === 'F') ? 2 : 1;
        $fatturaWeb = strtoupper($row[$headerMap['fatturaWEB'] ?? $headerMap['fatturaweb'] ?? $headerMap['FATTURAWEB'] ?? 5] ?? 'NO');

        // Costruisci la combinazione video
        $combination = $this->buildVideoCombination($offerCode->video_segment, $tipoFinale, $fatturaWeb === 'SI');

        return VideoCampaign::create([
            'email' => $email,
            'customer_name' => $customerName,
            'video_combination' => $combination,
            'video_type' => $offerCode->type,
            'offer_code' => $offerCode->code,
            'offer_name' => $offerCode->offer_name,
        ]);
    }

    protected function buildVideoCombination(string $videoSegment, int $tipoFinale, bool $hasFatturaWeb = false): array
    {
        $combination = [
            'benvenuto',
            $videoSegment,
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
