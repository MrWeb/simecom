<?php

namespace App\Providers;

use App\Mail\Transport\BrevoApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Brevo API transport
        Mail::extend('brevo', function (array $config) {
            return new BrevoApiTransport(
                $config['api_key'] ?? config('services.brevo.api_key')
            );
        });
    }
}
