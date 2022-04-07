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

$fileName = '/dummy-file.txt';
$dirName = '/dummy-dir';

// Test dummy file exist
if (!$flysystem->fileExists($fileName)) {
    // Create dummy file
    $flysystem->write($fileName, 'dummy file created by ' . __FILE__);
}

// Test dummy directory exist
if (!$flysystem->directoryExists($dirName)) {
    // Create dummy directory
    $flysystem->createDirectory($dirName);
}

// copy the dummy file
$flysystem->copy($fileName, $dirName . $fileName);

// Test copy
if (!$flysystem->fileExists($dirName . $fileName) && !$flysystem->fileExists($fileName)) {
    throw new \Exception('File ' . $dirName . $fileName . ' not exist on sharepoint [Move failed]', 500);
}

// Cleanup
$flysystem->delete($fileName);
$flysystem->delete($dirName . $fileName);
$flysystem->deleteDirectory($dirName);
