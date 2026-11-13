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
    $isNewDb = !file_exists($dbFile);
    if ($isNewDb) {
        @touch($dbFile);
        @chmod($dbFile, 0666);
    }

    // 3. Set environment overrides for Vercel
    putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
    putenv('SESSION_DRIVER=cookie');
    putenv('CACHE_STORE=array');
    putenv('CACHE_DRIVER=array');
    $_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
    $_ENV['SESSION_DRIVER'] = 'cookie';
    $_ENV['CACHE_STORE'] = 'array';
    $_ENV['CACHE_DRIVER'] = 'array';

    if (getenv('DB_CONNECTION') === 'sqlite' || !getenv('DB_CONNECTION')) {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=' . $dbFile);
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $dbFile;
    }

    // 4. Autoload and Bootstrap Laravel
    require __DIR__ . '/../vendor/autoload.php';

    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // 5. Run migrations on cold start if SQLite database is fresh
    if ($isNewDb && (getenv('DB_CONNECTION') === 'sqlite' || !getenv('DB_CONNECTION'))) {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate --force');
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
