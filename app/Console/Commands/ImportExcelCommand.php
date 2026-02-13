<?php

namespace App\Console\Commands;

use App\Jobs\ProcessExcelJob;
use App\Services\CampaignImportSimulator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportExcelCommand extends Command
{
    protected $signature = 'campaign:import {file? : Path to Excel file (relative to storage/app or absolute). If omitted, processes all CSV files in storage/app/public/csv} {--skip-send : Skip sending email/SMS, only generate videos} {--dry-run : Simula importazione senza creare record}';

    protected $description = 'Import campaigns from a local Excel file or all CSV files in storage/app/public/csv';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            return $this->handleDryRun();
        }

        $file = $this->argument('file');
        $skipSend = $this->option('skip-send');

        // Se non viene passato un file, processa tutti i CSV in public/csv
        if (empty($file)) {
            return $this->processAllCsvFiles($skipSend);
        }

        return $this->processSingleFile($file, $skipSend);
    }

    protected function handleDryRun(): int
    {
        $simulator = new CampaignImportSimulator();
        $file = $this->argument('file');

        if (! empty($file)) {
            $fullPath = str_starts_with($file, '/') ? $file : storage_path("app/{$file}");

            if (! file_exists($fullPath)) {
                $this->error("File not found: {$fullPath}");
                return 1;
            }

            $result = $simulator->simulateFiles([$fullPath]);
        } else {
            $csvDir = storage_path('app/public/csv');

            if (! File::isDirectory($csvDir) || empty(File::glob("{$csvDir}/*.csv"))) {
                $this->info('Nessun file CSV trovato in storage/app/public/csv');
                return 0;
            }

            $result = $simulator->simulateAll();
        }

        $this->newLine();
        $this->info('=== SIMULAZIONE IMPORTAZIONE (dry-run) ===');
        $this->newLine();

        // File analizzati
        if (! empty($result->filesProcessed)) {
            $this->info('File analizzati: ' . implode(', ', $result->filesProcessed));
            $this->newLine();
        }

        // Riepilogo righe
        $this->table(
            ['Metrica', 'Valore'],
            [
                ['Righe totali', $result->totalRows],
                ['Righe valide', $result->validRows],
                ['Righe scartate', $result->skippedRows],
            ]
        );

        // Video
        $this->table(
            ['Video', 'Valore'],
            [
                ['Dinamici', $result->dynamicVideos],
                ['Statici', $result->staticVideos],
                ['Combinazioni uniche', $result->uniqueCombinations],
            ]
        );

        // Canali
        $this->table(
            ['Canale', 'Valore'],
            [
                ['Email', $result->emailCount],
                ['SMS', $result->smsCount],
                ['Allegati', $result->attachmentCount],
            ]
        );

        // Scarti per tipo
        if (! empty($result->skippedByType)) {
            $this->newLine();
            $this->warn('Dettaglio scarti:');
            $skipRows = [];
            foreach ($result->skippedByType as $type => $count) {
                $label = match ($type) {
                    'missing_contact' => 'Email e telefono mancanti',
                    'missing_offer_code' => 'Codice offerta non trovato',
                    'missing_attachment' => 'Allegato mancante',
                    default => $type,
                };
                $skipRows[] = [$label, $count];
            }
            $this->table(['Tipo', 'Conteggio'], $skipRows);
        }

        // Per codice offerta
        if (! empty($result->countPerOfferCode)) {
            $this->newLine();
            $this->info('Per codice offerta:');
            $offerRows = [];
            foreach ($result->countPerOfferCode as $code => $count) {
                $offerRows[] = [$code, $count];
            }
            $this->table(['Codice offerta', 'Conteggio'], $offerRows);
        }

        // Segmenti mancanti
        if ($result->missingSegmentsCount > 0) {
            $this->newLine();
            $this->error("ATTENZIONE: {$result->missingSegmentsCount} segmenti video non trovati su disco!");
        }

        $this->newLine();
        $this->info('Nessun record creato nel database. Nessun file rinominato.');

        return 0;
    }

    protected function processSingleFile(string $file, bool $skipSend): int
    {
        $storagePath = storage_path('app');

        // Se è un path assoluto
        if (str_starts_with($file, '/')) {
            $fullPath = $file;
        } else {
            $fullPath = "{$storagePath}/{$file}";
        }

        if (! file_exists($fullPath)) {
            $this->error("File not found: {$fullPath}");
            return 1;
        }

        $this->info("Processing: {$fullPath}");
        if ($skipSend) {
            $this->info('Email/SMS sending will be skipped (video only mode)');
        }

        ProcessExcelJob::dispatch($fullPath, $skipSend);

        $this->info('Job dispatched to queue!');

        return 0;
    }

    protected function processAllCsvFiles(bool $skipSend): int
    {
        $csvDir = storage_path('app/public/csv');

        if (! File::isDirectory($csvDir)) {
            $this->error("Directory not found: {$csvDir}");
            return 1;
        }

        $csvFiles = File::glob("{$csvDir}/*.csv");

        if (empty($csvFiles)) {
            $this->info('No CSV files found in storage/app/public/csv');
            return 0;
        }

        $this->info('Found ' . count($csvFiles) . ' CSV file(s) in storage/app/public/csv');

        if ($skipSend) {
            $this->info('Email/SMS sending will be skipped (video only mode)');
        }

        foreach ($csvFiles as $csvFile) {
            // Rinomina il file in .csv.back PRIMA di dispatchare il job
            // così il job riceve il path corretto al file esistente
            $backupPath = $csvFile . '.back';
            File::move($csvFile, $backupPath);

            $this->info("Processing: {$backupPath}");

            ProcessExcelJob::dispatch($backupPath, $skipSend);
        }

        $this->info('All jobs dispatched to queue!');

        return 0;
    }
}
