Checkfront PHP SDK (v2.1)
==========================

The [Checkfront Booking API](http://www.checkfront.com/developers/api/) allows you 
to build integrations and custom applications that interact with a remote Checkfront account.

This repository contains the open source PHP SDK that allows you to utilize the
above on your website. Except as otherwise noted, the Checkfront PHP SDK
is licensed under the Apache Licence, Version 2.0
(http://www.apache.org/licenses/LICENSE-2.0.html)

Features
--------

The Checkfront API SDK provides the following functionality:

* OAuth2 authorization and authentication.
* OAuth2 token refresh.
* Session handing.
* CQL query interface.
* Access to Checkfront Objects via GET, POST, PUT and DELETE request.

Usage
-----

The examples are a good place to start. The minimal you'll need to
have is:

```php
<?
$Checkfront = new Checkfront(
    array(
        'host'=>'demo.checkfront.com',
        'consumer_key'  => '5010076404ec1809470508',
        'consumer_secret' => 'ba0a5c0c509445024c374fcd264d41e816b02d4e',
        'redirect_uri'=>'oob',
        'access_token' => 'c43c9acf2e209de70f1190d3b7290592',
        'mode' => 'public',
    )
);


/* Get list of countries */
if($data = $Checkfront->cql('select * from country')) {
    print_r($data);
} elseif($Checkfront->error) {
    print "Error: \n" . var_export($Checkfront->error,true);
}
?>
```

```php
<?
/* Get items rates and availbility*/
$Checkfront->get('item',array('start_date'=>date('Y-m-d'),'end_date'=>date(strtotime('+3 days')));
?>
```
