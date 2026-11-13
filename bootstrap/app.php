<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\TenantContextMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

if (
    isset($_ENV['VERCEL_ENV']) ||
    isset($_SERVER['VERCEL_ENV']) ||
    isset($_ENV['VERCEL']) ||
    isset($_SERVER['VERCEL']) ||
    getenv('VERCEL') ||
    getenv('APP_STORAGE')
) {
    $app->useStoragePath(getenv('APP_STORAGE') ?: '/tmp/storage');
}

return $app;
