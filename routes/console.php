<?php

use App\Jobs\DownloadExcelFromFtpJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Import campagne giornaliero alle 17:00
//Schedule::command('campaign:import')->dailyAt('17:00');
