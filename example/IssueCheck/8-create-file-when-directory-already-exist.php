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

// Create directory
$dirName = '/dummy';
$flysystem->createDirectory($dirName);

$fileName = '/dummy/file/dummy-file-' . (new DateTime())->format('YmdHis') . '.txt';

// Create dummy file
$flysystem->write($fileName, 'dummy file created by ' . __FILE__);

// Test dummy file exist
if ($flysystem->fileExists($fileName)) {
    echo 'File ' . $fileName . " created on sharepoint\n";
} else {
    throw new \Exception('File ' . $fileName . ' not exist on sharepoint', 500);
}

// Cleanup
//$flysystem->delete($fileName);
