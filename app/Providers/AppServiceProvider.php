<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AiAnalysisService;
use App\Services\PdfTextExtractor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Using class constants ensures that if you rename a class 
     * using an IDE, these references update automatically.
     */
    public function register(): void
    {
        $this->app->singleton(AiAnalysisService::class);
        $this->app->singleton(PdfTextExtractor::class);
    }

    public function boot(): void
    {
        // Global configuration logic goes here.
    }
}