<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'https://www.simecom.it/');

// Landing page video personalizzato
Route::get('/v/{uuid}', [LandingController::class, 'show'])->name('video.landing');
