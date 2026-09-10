<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\VerifierCompteActif;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
            'compte.actif' => VerifierCompteActif::class,
        ]);

        $middleware->web(append: [
            VerifierCompteActif::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Les erreurs métier remontent en message clair, jamais en trace technique.
        $exceptions->render(function (\App\Exceptions\RegleMetierException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'motifs' => $e->motifs(),
                ], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        });
    })->create();
