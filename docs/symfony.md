# How to use with symfony

## Installation

You can install via composer:

``` bash
composer require league/flysystem-bundle enabel/flysystem-sharepoint
```

## Usage

* Configure your own parameters in `.env` or `.env.local`

```dotenv
SP_TENANT_ID='xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'
SP_CLIENT_ID='xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx' # Sensitive information !!!
SP_CLIENT_SECRET='xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx' # Sensitive information !!!
SP_SHAREPOINT_SITE='your-path-to-your-site'
SP_SHAREPOINT_DRIVE='your-library-name' # Leave blank if you use the default `Shared Documents`
```

* Create a connector service in `/config/services.yaml`

```yaml
Enabel\Sharepoint\Flysystem\SharepointConnector:
    arguments:
        $tenantId: '%env(resolve:SP_TENANT_ID)%'
        $clientId: '%env(resolve:SP_CLIENT_ID)%'
        $clientSecret: '%env(resolve:SP_CLIENT_SECRET)%'
        $sharepointSite: '%env(resolve:SP_SHAREPOINT_SITE)%'
        $sharepointDrive: '%env(resolve:SP_SHAREPOINT_DRIVE)%'
```

* Use this connector for the sharepoint adapter `/config/services.yaml`

```yaml
Enabel\Sharepoint\Flysystem\FlysystemSharepointAdapter:
  arguments:
    $connector: '@Enabel\Sharepoint\Flysystem\SharepointConnector'
```

* Configure this adapter as storage in flysystem `/config/packages/flysystem.yaml`

```yaml
flysystem:
  storages:
    sharepoint.storage:
      adapter: 'Enabel\Sharepoint\Flysystem\FlysystemSharepointAdapter'
```

* Then you can use flysystem as abstraction for the filesystem, 
  see [Bundle documentation](https://github.com/thephpleague/flysystem-bundle)