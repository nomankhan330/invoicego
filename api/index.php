<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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
        Artisan::call('migrate --force');
    } catch (\Throwable $e) {
        error_log('Vercel cold start migration: ' . $e->getMessage());
    }
}

// 6. Handle HTTP Request
$app->handleRequest(Request::capture());
