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

    // 2. Detect Database Connection (External Cloud MySQL / MariaDB / Postgres vs fallback SQLite)
    $readEnv = static function (string $key): string {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string) $_SERVER[$key];
        }
        $val = getenv($key);
        return ($val !== false && $val !== '') ? (string) $val : '';
    };

    $dbUrl = $readEnv('DB_URL');
    $dbConn = $readEnv('DB_CONNECTION');
    $dbHost = $readEnv('DB_HOST');

    if (!$dbConn && $dbUrl) {
        if (str_starts_with($dbUrl, 'mysql://') || str_starts_with($dbUrl, 'mysql2://')) {
            $dbConn = 'mysql';
        } elseif (str_starts_with($dbUrl, 'postgres://') || str_starts_with($dbUrl, 'postgresql://')) {
            $dbConn = 'pgsql';
        }
    }

    if ((!$dbConn || $dbConn === 'sqlite') && $dbHost && !in_array($dbHost, ['127.0.0.1', 'localhost'], true)) {
        $dbConn = 'mysql';
    }

    if (!$dbConn) {
        $dbConn = 'sqlite';
    }

    // Prepare writable SQLite database in /tmp ONLY when running in fallback sqlite mode
    $dbFile = '/tmp/database.sqlite';
    if ($dbConn === 'sqlite') {
        $seededTemplate = __DIR__ . '/../database/database.sqlite';

        if (!file_exists($dbFile) || filesize($dbFile) === 0) {
            if (file_exists($seededTemplate) && filesize($seededTemplate) > 0) {
                @copy($seededTemplate, $dbFile);
            } else {
                @touch($dbFile);
            }
            @chmod($dbFile, 0666);
        }
    }

    // 3. Set environment overrides for Vercel
    $bootstrapCache = '/tmp/bootstrap/cache';
    $envOverrides = [
        'APP_ENV' => $readEnv('APP_ENV') ?: 'production',
        'APP_DEBUG' => $readEnv('APP_DEBUG') ?: 'false',
        'APP_KEY' => $readEnv('APP_KEY') ?: 'base64:thVoqnDOi6ATwz6gx/uIiEul1sfNELiQx5N4o+HBAHE=',
        'APP_STORAGE' => '/tmp/storage',
        'APP_PACKAGES_CACHE' => $bootstrapCache . '/packages.php',
        'APP_SERVICES_CACHE' => $bootstrapCache . '/services.php',
        'APP_CONFIG_CACHE' => $bootstrapCache . '/config.php',
        'APP_ROUTES_CACHE' => $bootstrapCache . '/routes-v7.php',
        'APP_EVENTS_CACHE' => $bootstrapCache . '/events.php',
        'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
        'SESSION_DRIVER' => $readEnv('SESSION_DRIVER') ?: 'cookie',
        'CACHE_STORE' => $readEnv('CACHE_STORE') ?: 'array',
        'CACHE_DRIVER' => $readEnv('CACHE_DRIVER') ?: 'array',
        'LOG_CHANNEL' => 'stderr',
        'DB_CONNECTION' => $dbConn,
        'DB_PERSISTENT' => in_array($dbConn, ['mysql', 'mariadb'], true) ? 'true' : 'false',
    ];

    if (isset($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
        $envOverrides['APP_URL'] = $scheme . '://' . $_SERVER['HTTP_HOST'];
    }

    if ($dbConn === 'sqlite') {
        $envOverrides['DB_DATABASE'] = $dbFile;
    }

    // Auto-configure MySQL TLS CA certificate for Cloud MySQL providers (TiDB Cloud, Aiven, PlanetScale, etc.)
    if (in_array($dbConn, ['mysql', 'mariadb'], true) && $readEnv('DB_SSL') !== 'false') {
        $customPem = $readEnv('MYSQL_ATTR_SSL_CA_CERT');
        if ($customPem) {
            $caPath = '/tmp/mysql-ca.pem';
            if (!file_exists($caPath)) {
                @file_put_contents($caPath, str_replace('\n', "\n", $customPem));
            }
            $envOverrides['MYSQL_ATTR_SSL_CA'] = $caPath;
        } elseif (!$readEnv('MYSQL_ATTR_SSL_CA')) {
            foreach ([
                '/etc/ssl/certs/ca-certificates.crt',
                '/etc/pki/tls/certs/ca-bundle.crt',
                '/etc/ssl/cert.pem',
            ] as $sysCa) {
                if (file_exists($sysCa)) {
                    $envOverrides['MYSQL_ATTR_SSL_CA'] = $sysCa;
                    break;
                }
            }
        }
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

    // 5. Auto-migrate ONLY for ephemeral SQLite fallback (or when AUTO_MIGRATE=true).
    // Skipping Artisan migrate on Cloud MySQL cold starts saves ~1.5-2.5s per new serverless container!
    if ($dbConn === 'sqlite' || $readEnv('AUTO_MIGRATE') === 'true') {
        try {
            $migrationFiles = scandir(__DIR__ . '/../database/migrations') ?: [];
            $stampHash = md5($dbConn . '|' . $dbHost . '|' . $readEnv('DB_DATABASE') . '|' . implode(',', $migrationFiles));
            $migrationStamp = '/tmp/.invoicego_migrated_' . $stampHash;

            if (!file_exists($migrationStamp)) {
                $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
                $kernel->call('migrate', ['--force' => true]);

                $needsSeed = !\Illuminate\Support\Facades\Schema::hasTable('users')
                    || \App\Models\User::count() === 0;

                if ($needsSeed) {
                    $kernel->call('db:seed', ['--force' => true]);
                }

                @touch($migrationStamp);
            }
        } catch (\Throwable $e) {
            error_log('Vercel cold start db setup (' . $dbConn . '): ' . $e->getMessage());
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
