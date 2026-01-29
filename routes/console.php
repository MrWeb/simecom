<?php

use App\Jobs\DownloadExcelFromFtpJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Import campagne il 15 di ogni mese alle 9:00
Schedule::command('campaign:import')->monthlyOn(15, '09:00');
