<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\VideoTrackingController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'https://www.simecom.it/');

// Landing page video personalizzato
Route::get('/v/{uuid}', [LandingController::class, 'show'])->name('video.landing');
Route::post('/v/{uuid}/track', [VideoTrackingController::class, 'track'])
    ->name('video.track')
    ->middleware('throttle:30,1')
    ->withoutMiddleware(VerifyCsrfToken::class);
