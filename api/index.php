<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    // 1. Prepare writable /tmp directories for Vercel Serverless environment
    $dirs = [
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/logs',
        '/tmp/bootstrap/cache',
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    // 2. Prepare writable SQLite database in /tmp
    $dbFile = '/tmp/database.sqlite';
    $isNewDb = !file_exists($dbFile) || filesize($dbFile) === 0;
    if ($isNewDb) {
        @touch($dbFile);
        @chmod($dbFile, 0666);
    }

    // 3. Set environment overrides for Vercel
    $bootstrapCache = '/tmp/bootstrap/cache';
    $envOverrides = [
        'APP_ENV' => getenv('APP_ENV') ?: 'production',
        'APP_DEBUG' => getenv('APP_DEBUG') ?: 'true',
        'APP_KEY' => getenv('APP_KEY') ?: 'base64:thVoqnDOi6ATwz6gx/uIiEul1sfNELiQx5N4o+HBAHE=',
        'APP_STORAGE' => '/tmp/storage',
        'APP_PACKAGES_CACHE' => $bootstrapCache . '/packages.php',
        'APP_SERVICES_CACHE' => $bootstrapCache . '/services.php',
        'APP_CONFIG_CACHE' => $bootstrapCache . '/config.php',
        'APP_ROUTES_CACHE' => $bootstrapCache . '/routes-v7.php',
        'APP_EVENTS_CACHE' => $bootstrapCache . '/events.php',
        'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
        'SESSION_DRIVER' => 'cookie',
        'CACHE_STORE' => 'array',
        'CACHE_DRIVER' => 'array',
        'LOG_CHANNEL' => 'stderr',
    ];

    if (isset($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
        $envOverrides['APP_URL'] = $scheme . '://' . $_SERVER['HTTP_HOST'];
    }

    if (getenv('DB_CONNECTION') === 'sqlite' || !getenv('DB_CONNECTION')) {
        $envOverrides['DB_CONNECTION'] = 'sqlite';
        $envOverrides['DB_DATABASE'] = $dbFile;
    }

    foreach ($envOverrides as $key => $val) {
        putenv("{$key}={$val}");
        $_ENV[$key] = $val;
        $_SERVER[$key] = $val;
    }

    // 4. Autoload and Bootstrap Laravel
    require __DIR__ . '/../vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');

    // 5. Run migrations on cold start if SQLite database is fresh
    if ($isNewDb && (getenv('DB_CONNECTION') === 'sqlite' || !getenv('DB_CONNECTION'))) {
        try {
            $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
            $kernel->call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            error_log('Vercel cold start migration: ' . $e->getMessage());
        }
    }

    // 6. Handle HTTP Request
    $app->handleRequest(\Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<div style="font-family:sans-serif;padding:30px;background:#FEF2F2;border:1px solid #F87171;border-radius:8px;max-width:850px;margin:40px auto;color:#991B1B;">';
    echo '<h2 style="margin-top:0;">Vercel Serverless PHP Error</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Location:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre style="background:#FFF;padding:15px;border-radius:4px;overflow:auto;font-size:12px;color:#1F2937;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
    exit;
}
