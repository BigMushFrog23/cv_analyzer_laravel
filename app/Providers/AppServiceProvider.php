<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Enregistrer les Services dans le conteneur IoC de Laravel
        // Laravel les injectera automatiquement dans les constructeurs des contrôleurs
        $this->app->singleton(\App\Services\AiAnalysisService::class);
        $this->app->singleton(\App\Services\PdfTextExtractor::class);
    }

    public function boot(): void
    {
        //
    }
}
