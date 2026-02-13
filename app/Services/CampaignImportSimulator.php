<?php

namespace App\Services;

use App\DTOs\SimulationResult;
use App\Models\OfferCode;
use App\Models\VideoCampaign;
use Illuminate\Support\Facades\File;

class CampaignImportSimulator
{
    protected array $offerCodesCache = [];

    protected string $segmentsBasePath;

    public function __construct()
    {
        $this->segmentsBasePath = storage_path('app/public/videos');
    }

    /**
     * Pre-carica tutti gli OfferCode attivi in memoria.
     */
    protected function preloadOfferCodes(): void
    {
        $this->offerCodesCache = OfferCode::where('active', true)
            ->get()
            ->keyBy(fn ($oc) => strtoupper($oc->code))
            ->all();
    }

    /**
     * Cerca un OfferCode dalla cache.
     */
    protected function findOfferCode(string $code): ?OfferCode
    {
        return $this->offerCodesCache[strtoupper($code)] ?? null;
    }

    /**
     * Simula tutti i CSV in storage/app/public/csv.
     */
    public function simulateAll(): SimulationResult
    {
        $csvDir = storage_path('app/public/csv');

        if (! File::isDirectory($csvDir)) {
            return new SimulationResult();
        }

        $csvFiles = File::glob("{$csvDir}/*.csv");

        if (empty($csvFiles)) {
            return new SimulationResult();
        }

        return $this->simulateFiles($csvFiles);
    }

    /**
     * Simula file specifici.
     */
    public function simulateFiles(array $filePaths): SimulationResult
    {
        $this->preloadOfferCodes();

        $totalRows = 0;
        $validRows = 0;
        $skippedRows = 0;
        $skippedByType = [];
        $emailCount = 0;
        $smsCount = 0;
        $attachmentCount = 0;
        $hashes = [];
        $countPerOfferCode = [];
        $dynamicVideos = 0;
        $staticVideos = 0;
        $missingSegments = [];
        $missingOfferCodes = [];
        $filesProcessed = [];

        foreach ($filePaths as $filePath) {
            $filename = basename($filePath);
            $filesProcessed[] = $filename;

            $hasAttachments = str_ends_with(
                preg_replace('/\.back$/', '', $filename),
                '_allegati.csv'
            );

            $rows = $this->readFile($filePath);

            if (empty($rows)) {
                continue;
            }

            $header = array_shift($rows);
            $headerMap = array_flip($header);

            foreach ($rows as $row) {
                $totalRows++;

                $result = $this->simulateRow($row, $headerMap, $header, $hasAttachments);

                if ($result === null) {
                    // Riga completamente vuota, ignorata
                    $totalRows--;
                    continue;
                }

                if ($result['skipped']) {
                    $skippedRows++;
                    $type = $result['error_type'];
                    $skippedByType[$type] = ($skippedByType[$type] ?? 0) + 1;
                    if ($type === 'missing_offer_code' && ! empty($result['offer_code_value'])) {
                        $missingOfferCodes[$result['offer_code_value']] = ($missingOfferCodes[$result['offer_code_value']] ?? 0) + 1;
                    }
                    continue;
                }

                $validRows++;

                // Conteggio per codice offerta
                $offerCode = $result['offer_code'];
                $countPerOfferCode[$offerCode] = ($countPerOfferCode[$offerCode] ?? 0) + 1;

                // Canali
                if (! empty($result['email'])) {
                    $emailCount++;
                } elseif (! empty($result['phone'])) {
                    $smsCount++;
                }

                // Allegati
                if (! empty($result['attachment_path'])) {
                    $attachmentCount++;
                }

                // Combinazioni video uniche
                $hash = $result['video_hash'];
                $hashes[$hash] = true;

                // Dinamico vs statico
                if (in_array('__DYNAMIC_OFFER__', $result['video_combination'])) {
                    $dynamicVideos++;
                } else {
                    $staticVideos++;
                }

                // Controlla segmenti su disco
                foreach ($result['video_combination'] as $segment) {
                    if ($segment === '__DYNAMIC_OFFER__') {
                        continue;
                    }
                    if (! $this->segmentExistsOnDisk($segment, $result['video_type'])) {
                        $key = $result['video_type'] . '/' . $segment;
                        $missingSegments[$key] = true;
                    }
                }
            }
        }

        return new SimulationResult(
            totalRows: $totalRows,
            validRows: $validRows,
            skippedRows: $skippedRows,
            skippedByType: $skippedByType,
            dynamicVideos: $dynamicVideos,
            staticVideos: $staticVideos,
            uniqueCombinations: count($hashes),
            countPerOfferCode: $countPerOfferCode,
            emailCount: $emailCount,
            smsCount: $smsCount,
            attachmentCount: $attachmentCount,
            missingSegmentsCount: count($missingSegments),
            missingOfferCodes: $missingOfferCodes,
            filesProcessed: $filesProcessed,
        );
    }

    /**
     * Valida una singola riga (stessa logica di ProcessExcelJob::processRow).
     * Ritorna null se la riga è completamente vuota, altrimenti un array con i dati.
     */
    protected function simulateRow(array $row, array $headerMap, array $header, bool $hasAttachments): ?array
    {
        $email = $row[$headerMap['MAIL'] ?? $headerMap['mail'] ?? 0] ?? null;
        $nome = $row[$headerMap['nom'] ?? $headerMap['NOM'] ?? 1] ?? '';
        $cognome = $row[$headerMap['cog'] ?? $headerMap['COG'] ?? 2] ?? '';
        $codiceOfferta = $row[$headerMap['CODOF'] ?? $headerMap['codof'] ?? 3] ?? '';
        $sesso = $row[$headerMap['SEX'] ?? $headerMap['sex'] ?? 4] ?? 'M';

        $phone = $this->extractPhone($row, $headerMap);
        $codute = $row[$headerMap['CODUTE'] ?? $headerMap['codute'] ?? -1] ?? null;
        $customerName = trim("{$nome} {$cognome}");

        // Riga completamente vuota
        if (empty($email) && empty($phone) && empty($customerName) && empty($codiceOfferta)) {
            return null;
        }

        // Mancano sia email che telefono
        if (empty($email) && empty($phone)) {
            return ['skipped' => true, 'error_type' => 'missing_contact'];
        }

        // Cerca codice offerta
        $offerCode = $this->findOfferCode($codiceOfferta);

        if (! $offerCode) {
            return ['skipped' => true, 'error_type' => 'missing_offer_code', 'offer_code_value' => $codiceOfferta];
        }

        $tipoFinale = ($sesso === 'F') ? 2 : 1;
        $fatturaWeb = strtoupper($row[$headerMap['fatturaWEB'] ?? $headerMap['fatturaweb'] ?? $headerMap['FATTURAWEB'] ?? 5] ?? 'NO');

        $combination = $this->buildVideoCombination($offerCode->video_segment, $tipoFinale, $fatturaWeb === 'SI');

        // Gestione allegati
        $attachmentPath = null;
        if ($hasAttachments) {
            if (empty($codute)) {
                return ['skipped' => true, 'error_type' => 'missing_attachment'];
            }

            $attachmentPath = $this->findAttachmentPdf($codute);

            if (! $attachmentPath) {
                return ['skipped' => true, 'error_type' => 'missing_attachment'];
            }
        }

        $videoType = $offerCode->type ?? 'luce';
        $hash = VideoCampaign::generateHash($combination, $videoType, $offerCode->offer_name);

        return [
            'skipped' => false,
            'email' => $email,
            'phone' => $phone,
            'offer_code' => $offerCode->code,
            'video_combination' => $combination,
            'video_type' => $videoType,
            'video_hash' => $hash,
            'attachment_path' => $attachmentPath,
        ];
    }

    /**
     * Controlla se un segmento video esiste su disco.
     */
    protected function segmentExistsOnDisk(string $slug, string $videoType): bool
    {
        $possibleNames = [
            "{$slug}.mp4",
            str_replace('_', '-', $slug) . '.mp4',
            str_replace('-', '_', $slug) . '.mp4',
        ];

        foreach ($possibleNames as $filename) {
            $fullPath = "{$this->segmentsBasePath}/{$videoType}/{$filename}";
            if (file_exists($fullPath)) {
                return true;
            }
        }

        return false;
    }

    protected function extractPhone(array $row, array $headerMap): ?string
    {
        $phoneColumns = ['CEL', 'cel', 'TEL', 'tel', 'TELEFONO', 'telefono', 'PHONE', 'phone', 'CELLULARE', 'cellulare', 'CELL', 'cell'];

        foreach ($phoneColumns as $col) {
            if (isset($headerMap[$col]) && ! empty($row[$headerMap[$col]])) {
                return trim($row[$headerMap[$col]]);
            }
        }

        return null;
    }

    protected function buildVideoCombination(?string $videoSegment, int $tipoFinale, bool $hasFatturaWeb = false): array
    {
        $combination = [
            'benvenuto',
            $videoSegment ?? '__DYNAMIC_OFFER__',
        ];

        if (! $hasFatturaWeb) {
            $combination[] = 'bolletta-digitale';
        }

        $combination[] = 'porta-un-amico';
        $combination[] = 'prodotti';

        if (! $hasFatturaWeb) {
            $combination[] = 'bolletta-digitale-2';
        } else {
            $combination[] = 'fine-1';
        }

        return $combination;
    }

    protected function readFile(string $fullPath): array
    {
        if (! file_exists($fullPath)) {
            return [];
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($extension === 'csv' || $extension === 'back') {
            return $this->readCsv($fullPath);
        }

        return [];
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

    protected function findAttachmentPdf(string $codute): ?string
    {
        $pdfDir = storage_path('app/public/csv/pdf');

        if (! is_dir($pdfDir)) {
            return null;
        }

        $pattern = $pdfDir . '/*_' . $codute . '.pdf';
        $files = glob($pattern);

        if (empty($files)) {
            return null;
        }

        return 'csv/pdf/' . basename($files[0]);
    }
}
