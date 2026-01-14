<?php

namespace App\Console\Commands;

use App\Jobs\ProcessExcelJob;
use Illuminate\Console\Command;

class ImportExcelCommand extends Command
{
    protected $signature = 'campaign:import {file : Path to Excel file (relative to storage/app or absolute)}';

    protected $description = 'Import campaigns from a local Excel file';

    public function handle(): int
    {
        $file = $this->argument('file');
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

        ProcessExcelJob::dispatch($fullPath);

        $this->info('Job dispatched to queue!');

        return 0;
    }
}
