<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$runtimeRoot = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'padelegan-lapor';
$storagePath = $runtimeRoot.DIRECTORY_SEPARATOR.'storage';

foreach ([
    $storagePath.DIRECTORY_SEPARATOR.'app',
    $storagePath.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'data',
    $storagePath.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'sessions',
    $storagePath.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'testing',
    $storagePath.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'views',
    $storagePath.DIRECTORY_SEPARATOR.'logs',
] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

$setRuntimeEnvironment = static function (string $key, string $value): void {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
};

if (getenv('VERCEL')) {
    foreach ([
        'SESSION_DRIVER' => 'cookie',
        'CACHE_STORE' => 'array',
        'QUEUE_CONNECTION' => 'sync',
    ] as $key => $value) {
        if (! getenv($key)) {
            $setRuntimeEnvironment($key, $value);
        }
    }
}

if (! getenv('VIEW_COMPILED_PATH')) {
    $setRuntimeEnvironment('VIEW_COMPILED_PATH', $storagePath.'/framework/views');
}

$vercelHost = getenv('VERCEL_PROJECT_PRODUCTION_URL') ?: getenv('VERCEL_URL');
$configuredUrl = getenv('APP_URL');

if ($vercelHost && (! $configuredUrl || $configuredUrl === 'http://localhost')) {
    $safeHost = preg_replace('/[^a-z0-9.-]/i', '', $vercelHost);

    if ($safeHost) {
        $setRuntimeEnvironment('APP_URL', 'https://'.$safeHost);
    }
}

require dirname(__DIR__).'/vendor/autoload.php';

/** @var Application $app */
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$app->useStoragePath($storagePath);
$app->handleRequest(Request::capture());
