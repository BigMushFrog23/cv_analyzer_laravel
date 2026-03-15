<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AiAnalysisService;
use App\Services\PdfTextExtractor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Define the services as an array to avoid repeating
     * '$this->app->singleton' multiple times.
     */
    private const APP_SERVICES = [
        AiAnalysisService::class,
        PdfTextExtractor::class,
    ];

    public function register(): void
    {
        foreach (self::APP_SERVICES as $service) {
            $this->app->singleton($service);
        }
    }

    public function boot(): void
    {
        // Global boot logic
    }
}
