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
        $env['SHAREPOINT_SITE']
    );
} else {
    throw new \Exception(
        'Variables "TENANT_ID", "CLIENT_ID", "CLIENT_SECRET", "SHAREPOINT_SITE" are mandatory in the .env file',
        500
    );
}

$adapter = new FlysystemSharepointAdapter($connector);

$flysystem = new Filesystem($adapter);

$dirName = '/dummy';

// Create dummy directory
$flysystem->createDirectory($dirName);

// Test dummy directory exist
if ($flysystem->directoryExists($dirName)) {
    echo 'Directory ' . $dirName . " created on sharepoint\n";
} else {
    throw new \Exception('Directory ' . $dirName . ' not exist on sharepoint', 500);
}

// Delete dummy directory
$flysystem->deleteDirectory($dirName);

// Test dummy file not exist
if (!$flysystem->directoryExists($dirName)) {
    echo 'Directory ' . $dirName . " deleted from sharepoint\n";
} else {
    throw new \Exception('Directory ' . $dirName . ' not deleted from sharepoint', 500);
}
