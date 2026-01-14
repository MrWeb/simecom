<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadExcelFromFtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(): void
    {
        $ftpPath = config('services.ftp.path', '/exports/daily.xlsx');
        $localPath = 'imports/daily_' . now()->format('Y-m-d') . '.xlsx';

        try {
            // Scarica file da FTP
            $contents = Storage::disk('ftp')->get($ftpPath);

            if (empty($contents)) {
                Log::warning('FTP file is empty or not found', ['path' => $ftpPath]);
                return;
            }

            // Salva localmente
            Storage::disk('local')->put($localPath, $contents);

            Log::info('Excel downloaded from FTP', [
                'ftp_path' => $ftpPath,
                'local_path' => $localPath,
                'size' => strlen($contents),
            ]);

            // Dispatcha il job di processing
            ProcessExcelJob::dispatch($localPath);

        } catch (\Exception $e) {
            Log::error('Failed to download Excel from FTP', [
                'path' => $ftpPath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
