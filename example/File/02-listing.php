<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use Enabel\Sharepoint\Flysystem\FlysystemSharepointAdapter;
use Enabel\Sharepoint\Flysystem\SharepointConnector;
use League\Flysystem\Filesystem;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$env = $dotenv->safeLoad();

if (isset($env['TENANT_ID'], $env['CLIENT_ID'], $env['CLIENT_SECRET'], $env['SHAREPOINT_SITE'])) {
    $connector = new SharepointConnector(
        $env['TENANT_ID'],
        $env['CLIENT_ID'],
        $env['CLIENT_SECRET'],
        $env['SHAREPOINT_SITE'],
        $env['SHAREPOINT_DRIVE']
    );
} else {
    throw new \Exception(
        'Variables "TENANT_ID", "CLIENT_ID", "CLIENT_SECRET", "SHAREPOINT_SITE" are mandatory in the .env file',
        500
    );
}

$adapter = new FlysystemSharepointAdapter($connector);

$flysystem = new Filesystem($adapter);

$path = '/';

// prepare some files
for ($i = 0; $i < 4; $i++) {
    $flysystem->write(
        $path . 'dummy-file-' . $i . '.txt',
        'dummy file ' . $i . ' created by ' . __FILE__
    );
}

// List content
$files = $flysystem->listContents($path);

echo count($files->toArray()) . " item(s) found in '" . $path . "' :\n";
foreach ($files as $file) {
    if (isset($file['file'])) {
        echo '- File: ' . $file['name'] . "\n";
    } else {
        echo '- Directory: ' . $file['name'] . "\n";
    }
}

// cleanup
for ($i = 0; $i < 4; $i++) {
    $flysystem->delete($path . 'dummy-file-' . $i . '.txt');
}
