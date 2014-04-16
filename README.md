# Checkfront PHP SDK (v3.0)


The [Checkfront Booking API](http://www.checkfront.com/developers/api/) allows you 
to build integrations and custom applications that interact with a remote Checkfront account.


##Updates

* The 'Cart' example has been given a few quick fixes for v3 API support, using token pair auth.
* The CheckfrontAPI library now supports connecting to the v3 API, as well as features like token pair authentication
* New/updated examples are in the works.
* See our v3 API documentation for more information.


##Features

The Checkfront API SDK provides the following functionality:

* OAuth2 authorization and authentication.
* OAuth2 token refresh.
* Token pair authorization.
* Session handing.
* Access to Checkfront Objects via GET, POST, PUT and DELETE request.


## Installation

At Checkfront to `composer.json` file. If you are not using [Composer](http://getcomposer.org), you should be. It's an excellent way to manage dependencies in your PHP application. 

```json
{
    "repositories": {
        "checkfront-unofficial": {
            "type": "package",
            "package": {
                "name": "Checkfront/PHP-SDK",
                "version": "3.0",
                "source": {
                    "url": "https://github.com/gegere/PHP-SDK.git",
                    "type": "git",
                    "reference": "origin/master"
                    }
                }
            }
    },
    "minimum-stability" : "dev",
    "require": {
        "Checkfront/PHP-SDK": "3.0.*"
        },
    "autoload": {
        "psr-0": {
            "Checkfront": "vendor/Checkfront/PHP-SDK/lib"
            }
        }
}
```

Then at the top of your PHP script require the autoloader:

```bash
require 'vendor/autoload.php';
```


#### Alternative: Install from zip

If you are not using Composer, simply download and install the **[latest packaged release of the library as a zip](https://github.com/gegere/PHP-SDK/archive/master.zip)**. 

Then require the library from package:

```php
require("path/to/Checkfront/Checkfront.php");
```


## Usage

The examples are a good place to start. The minimal you'll need to have is:

```php
<?
$Checkfront = new Checkfront(
    array(
        'host'=>'your-company.checkfront.com',
        'consumer_key'  => '5010076404ec1809470508',
        'consumer_secret' => 'ba0a5c0c509445024c374fcd264d41e816b02d4e',
        'redirect_uri'=>'oob',
    )
);
?>
```

```php
<?
/* Get items rates and availbility*/
$Checkfront->get('item',array(
							  'start_date'=>date('Y-m-d'),
							  'end_date'=>date('Y-m-d',strtotime('+3 days'))
							 )
				);
?>
```
