<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Subdirectory URI normalization: Ensure trailing slash if accessed at base subdirectory (e.g. /it-system -> /it-system/)
if (isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['REQUEST_URI'])) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($scriptDir !== '/' && $scriptDir !== '' && $scriptDir !== '.') {
        $reqParts = explode('?', $_SERVER['REQUEST_URI'], 2);
        if ($reqParts[0] === $scriptDir) {
            $queryString = isset($reqParts[1]) ? '?' . $reqParts[1] : '';
            header('Location: ' . $scriptDir . '/' . $queryString, true, 301);
            exit;
        }
    }
}

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Bind public path to current directory so assets and manifests work seamlessly
$app->usePublicPath(__DIR__);

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);