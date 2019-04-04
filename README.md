Checkfront PHP SDK (v3.0)
==========================

The [Checkfront Booking API](http://www.checkfront.com/developers/api/) allows you 
to build integrations and custom applications that interact with a remote Checkfront account.

This repository contains the open source PHP SDK that allows you to utilize the
above on your website. Except as otherwise noted, the Checkfront PHP SDK
is licensed under the Apache Licence, Version 2.0
(http://www.apache.org/licenses/LICENSE-2.0.html)

Updates
-------

See [CHANGELOG.md](CHANGELOG.md)


Features
--------

The Checkfront API SDK provides the following functionality:

* OAuth2 authorization and authentication.
* OAuth2 token refresh.
* Token pair authorization.
* Session handing.
* Access to Checkfront Objects via GET, POST, PUT and DELETE request.

Usage
-----

The examples are a good place to start. The minimal you'll need to
have is:

##### OAuth2 Access

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

##### Token Access

```php
<?
$Checkfront = new Checkfront(
    array(
        'host'=>'your-company.checkfront.com',
	'auth_type' => 'token',
        'api_key'  => '5010076404ec1809470508',
        'api_secret' => 'ba0a5c0c509445024c374fcd264d41e816b02d4e',
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
));
?>
```
