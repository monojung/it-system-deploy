<?php

if (!is_writable(__DIR__ . '/cache') || (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {
    $bootstrapCacheDir = dirname(__DIR__) . '/storage/framework/bootstrap_cache';
    if (!is_dir($bootstrapCacheDir)) {
        @mkdir($bootstrapCacheDir, 0777, true);
    }
    $_ENV['APP_PACKAGES_CACHE'] = 'storage/framework/bootstrap_cache/packages.php';
    $_ENV['APP_SERVICES_CACHE'] = 'storage/framework/bootstrap_cache/services.php';
    $_ENV['APP_CONFIG_CACHE'] = 'storage/framework/bootstrap_cache/config.php';
    $_ENV['APP_ROUTES_CACHE'] = 'storage/framework/bootstrap_cache/routes-v7.php';
    $_ENV['APP_EVENTS_CACHE'] = 'storage/framework/bootstrap_cache/events.php';
    putenv('APP_PACKAGES_CACHE=' . $_ENV['APP_PACKAGES_CACHE']);
    putenv('APP_SERVICES_CACHE=' . $_ENV['APP_SERVICES_CACHE']);
    putenv('APP_CONFIG_CACHE=' . $_ENV['APP_CONFIG_CACHE']);
    putenv('APP_ROUTES_CACHE=' . $_ENV['APP_ROUTES_CACHE']);
    putenv('APP_EVENTS_CACHE=' . $_ENV['APP_EVENTS_CACHE']);
}

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

return $app;
