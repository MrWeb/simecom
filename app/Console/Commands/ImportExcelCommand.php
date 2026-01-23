<?php

namespace App\Console\Commands;

use App\Jobs\ProcessExcelJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportExcelCommand extends Command
{
    protected $signature = 'campaign:import {file? : Path to Excel file (relative to storage/app or absolute). If omitted, processes all CSV files in storage/app/public/csv} {--skip-send : Skip sending email/SMS, only generate videos}';

    protected $description = 'Import campaigns from a local Excel file or all CSV files in storage/app/public/csv';

    public function handle(): int
    {
        $file = $this->argument('file');
        $skipSend = $this->option('skip-send');

        // Se non viene passato un file, processa tutti i CSV in public/csv
        if (empty($file)) {
            return $this->processAllCsvFiles($skipSend);
        }

        return $this->processSingleFile($file, $skipSend);
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
