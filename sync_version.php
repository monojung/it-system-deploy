<?php
/**
 * scripts/sync_version.php
 * Synchronize Application Version across config/version.php, .env, and it_settings database table.
 * Usage: php scripts/sync_version.php [version] [build]
 */

$baseDir = dirname(__DIR__);
$versionArg = $argv[1] ?? null;
$buildArg = $argv[2] ?? null;

$versionConfigFile = $baseDir . '/config/version.php';
$envFile = $baseDir . '/.env';

if (!file_exists($versionConfigFile)) {
    fwrite(STDERR, "[ERROR] config/version.php not found at: {$versionConfigFile}\n");
    exit(1);
}

// 1. Read existing config
$configContent = file_get_contents($versionConfigFile);
$currentVersion = '2.5.2';
if (preg_match("/'version'\s*=>\s*env\(['\"]APP_VERSION['\"],\s*['\"]([^'\"]+)['\"]\)/", $configContent, $m)) {
    $currentVersion = $m[1];
} elseif (preg_match("/'version'\s*=>\s*['\"]([^'\"]+)['\"]/", $configContent, $m)) {
    $currentVersion = $m[1];
}

$targetVersion = $versionArg ? trim($versionArg) : $currentVersion;
$today = date('Y-m-d');
$todayDateNum = date('Ymd');

// Determine build
if ($buildArg) {
    $targetBuild = trim($buildArg);
} else {
    // If build matches today, increment sequence or keep current
    if (preg_match("/'build'\s*=>\s*env\(['\"]APP_BUILD['\"],\s*['\"]([^'\"]+)['\"]\)/", $configContent, $bm)) {
        $existingBuild = $bm[1];
        if (str_starts_with($existingBuild, $todayDateNum)) {
            $parts = explode('.', $existingBuild);
            $seq = isset($parts[1]) ? (int)$parts[1] + 1 : 1;
            $targetBuild = $todayDateNum . '.' . $seq;
        } else {
            $targetBuild = $todayDateNum . '.1';
        }
    } else {
        $targetBuild = $todayDateNum . '.1';
    }
}

echo "=== System Version Synchronizer ===\n";
echo "Target Version: {$targetVersion}\n";
echo "Target Build:   {$targetBuild}\n";
echo "Release Date:   {$today}\n\n";

// 2. Update config/version.php
$updatedConfig = preg_replace(
    "/'version'\s*=>\s*env\(['\"]APP_VERSION['\"],\s*['\"][^'\"]+['\"]\)/",
    "'version' => env('APP_VERSION', '{$targetVersion}')",
    $configContent
);

$updatedConfig = preg_replace(
    "/'build'\s*=>\s*env\(['\"]APP_BUILD['\"],\s*['\"][^'\"]+['\"]\)/",
    "'build' => env('APP_BUILD', '{$targetBuild}')",
    $updatedConfig
);

$updatedConfig = preg_replace(
    "/'release_date'\s*=>\s*['\"][^'\"]+['\"]/",
    "'release_date' => '{$today}'",
    $updatedConfig
);

if ($updatedConfig !== $configContent) {
    file_put_contents($versionConfigFile, $updatedConfig);
    echo "[OK] Updated config/version.php with version {$targetVersion}, build {$targetBuild}\n";
} else {
    echo "[SKIP] config/version.php already up to date\n";
}

// 3. Update .env if present
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/^APP_VERSION=.*$/m', $envContent)) {
        $envContent = preg_replace('/^APP_VERSION=.*$/m', "APP_VERSION={$targetVersion}", $envContent);
    } else {
        $envContent .= "\nAPP_VERSION={$targetVersion}\n";
    }

    if (preg_match('/^APP_BUILD=.*$/m', $envContent)) {
        $envContent = preg_replace('/^APP_BUILD=.*$/m', "APP_BUILD={$targetBuild}", $envContent);
    } else {
        $envContent .= "APP_BUILD={$targetBuild}\n";
    }

    file_put_contents($envFile, $envContent);
    echo "[OK] Updated .env with APP_VERSION={$targetVersion}, APP_BUILD={$targetBuild}\n";
}

// 4. Update it_settings in database
try {
    require_once $baseDir . '/vendor/autoload.php';
    $app = require_once $baseDir . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    if (Illuminate\Support\Facades\Schema::hasTable('it_settings')) {
        \App\Models\SystemSetting::set('app_version', $targetVersion, 'system', 'text', 'Application Version');
        \App\Models\SystemSetting::set('app_build', $targetBuild, 'system', 'text', 'Application Build');
        echo "[OK] Updated it_settings table app_version = {$targetVersion}, app_build = {$targetBuild}\n";
    }

    // Clear caches
    Illuminate\Support\Facades\Artisan::call('config:clear');
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo "[OK] Cleared Laravel config and application cache\n";
} catch (Throwable $e) {
    echo "[NOTE] Database/Artisan bootstrap skipped: " . $e->getMessage() . "\n";
}

echo "\n[DONE] Version synchronization complete for v{$targetVersion}!\n";
