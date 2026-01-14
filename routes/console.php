<?php

use App\Jobs\DownloadExcelFromFtpJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Job giornaliero per scaricare e processare Excel da FTP
Schedule::job(new DownloadExcelFromFtpJob)->dailyAt('06:00');
