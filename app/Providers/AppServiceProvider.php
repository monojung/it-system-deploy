<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Cache probe result during PHP process lifecycle
     */
    private static ?bool $primaryDbHostHealthy = null;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.custom');
        Paginator::defaultSimpleView('vendor.pagination.custom');

        // 1. Dynamic Database Host Failover & Resilience
        $this->configureDatabaseFailover();

        // 2. Resilient Database Session Handler (graceful file fallback if DB fails)
        $this->registerResilientSessionDriver();
    }

    /**
     * Probes primary DB_HOST and falls back to DB_FALLBACK_HOST if connection is refused.
     */
    private function configureDatabaseFailover(): void
    {
        $primaryHost = config('database.connections.mysql.host');
        $fallbackHost = env('DB_FALLBACK_HOST', '127.0.0.1');

        if (!$primaryHost || !$fallbackHost || $primaryHost === $fallbackHost) {
            return;
        }

        // Use cached result if already determined in this process
        if (self::$primaryDbHostHealthy === true) {
            return;
        }
        if (self::$primaryDbHostHealthy === false) {
            config(['database.connections.mysql.host' => $fallbackHost]);
            return;
        }

        $port = (int) config('database.connections.mysql.port', 3306);
        $errno = 0;
        $errstr = '';

        // Fast 250ms probe to verify if primary host accepts connections on MySQL port
        $socket = @fsockopen($primaryHost, $port, $errno, $errstr, 0.25);
        if ($socket) {
            fclose($socket);
            self::$primaryDbHostHealthy = true;
        } else {
            self::$primaryDbHostHealthy = false;
            config(['database.connections.mysql.host' => $fallbackHost]);
            Log::info("Primary DB host {$primaryHost}:{$port} refused connection. Switched to fallback host: {$fallbackHost}");
        }
    }

    /**
     * Registers a resilient session driver that gracefully falls back to files if database fails.
     */
    private function registerResilientSessionDriver(): void
    {
        Session::extend('database', function ($app) {
            $table = $app['config']['session.table'] ?? 'it_sessions';
            $lifetime = $app['config']['session.lifetime'] ?? 120;
            $connectionName = $app['config']['session.connection'] ?? null;
            $connection = $app['db']->connection($connectionName);

            $dbHandler = new \Illuminate\Session\DatabaseSessionHandler(
                $connection, $table, $lifetime, $app
            );

            $fileHandler = new \Illuminate\Session\FileSessionHandler(
                $app['files'],
                $app['config']['session.files'] ?? storage_path('framework/sessions'),
                $lifetime
            );

            $resilientHandler = new \App\Services\ResilientDatabaseSessionHandler($dbHandler, $fileHandler);

            return $resilientHandler;
        });
    }
}
