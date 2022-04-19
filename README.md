# Flysystem adapter for Sharepoint

This package contains an adapter for Flysystem to work with Sharepoint (Graph API)

## Requirement
* You need PHP 8.0 or newer, with json support.

## Installation

You can install the package via composer:

``` bash
composer require enabel/flysystem-sharepoint
```

## Usage

You need to request a new clientId and clientSecret for a new application on Azure.

1. Go to `Azure portal` https://portal.azure.com
2. Go to `Active Directory`
3. Go to `App registrations`
4. Click on `new Registration` and follow the wizard.  
   (give it a name like 'sharepoint-connector-APP_NAME' and make a decision on the supported accounts, single tenant should be enough but this depends on your organisation)
5. When created the application is created write down the following details
6. 'Application (client) id', this will be your `$clientId`
7. 'Directory (tenant) id', this will be your `$tenantId`
8. Then we go in the menu to the `API permissions` to set the permissions that are required
9. The click on `Add a permission` and add the following permissions:  
   Microsoft Graph:
    - Files.ReadWrite.All
    - Sites.ReadWrite.All
    - User.Read
10. Click on the `Grant admin consent for ...Company...`
11. Go in the menu to `Certificates & secrets`
12. Click on `new client secret`
13. Give it a description and expiry date and the value will be your `$clientSecret`
14. The last parameter will be the sharepoint 'slug', this is part of the url of the sharepoint site what you want to use and creation of sharepoint site is out of scope of this readme.  
    When you sharepoint url is like `https://{tenant}.sharepoint.com/sites/{site-slug}/Shared%20Documents/Forms/AllItems.aspx`  
    You need to set the `$sharepointSite` as `{site-slug}`

Example:    
- Sharepoint site url: `https://enabelbe.sharepoint.com/sites/test-storage/Shared%20Documents/Forms/AllItems.aspx`
- Sharepoint site variable:  `$sharepointSite = 'test-storage'`

    
### Use standalone   

The documentation is available [here](./docs/standalone.md).

### Use with Symfony

The documentation is available [here](./docs/symfony.md).

## Tests

``` bash
$ make tests
```

## License

This project is open-sourced software licensed under the [GNU AGPLv3 License](LICENSE).

## About
Originally designed by [Damien Lagae][1]. If you have any questions, please contact [Damien Lagae][1].

[1]: damien.lagae@enabel.be