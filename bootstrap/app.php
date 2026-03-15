<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException; // For your custom handler
use Illuminate\Auth\AuthenticationException;             // For your custom handler

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        $exceptions->render(function (ModelNotFoundException $e) {
            Log::info("Access attempt to missing model: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', '...');
        });

        $exceptions->render(function (AuthenticationException $e) {
            // We use $e to log the event, making it a "used" variable
            Log::info('Session expired for a user: ' . $e->getMessage());

            return redirect()->route('login')
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        });
        
    })->create();
