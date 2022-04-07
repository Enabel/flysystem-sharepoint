# How to use standalone

## Installation

You can install via composer:

``` bash
composer require league/flysystem enabel/flysystem-sharepoint
```

## Usage

``` php
use Enabel\Sharepoint\Flysystem\FlysystemSharepointAdapter;
use Enabel\Sharepoint\Flysystem\SharepointConnector;
use League\Flysystem\Filesystem;

$tenantId = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
$clientId = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
$clientSecret = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
$sharepointSite = 'your-path-to-your-site';

$connector = new SharepointConnector($tenantId, $clientId, $clientSecret, $sharepointSite);

$adapter = new FlysystemSharepointAdapter($connector);

$flysystem = new Filesystem($adapter);
```
