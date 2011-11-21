Geocoder
========

**Geocoder** is a library which helps you build geo-aware applications. It provides an abstraction layer for geocoding manipulations.
The library is splitted in two parts: `HttpAdapter` and `Provider` and is really extensible.

[![Build Status](https://secure.travis-ci.org/willdurand/Geocoder.png)](http://travis-ci.org/willdurand/Geocoder)


### HttpAdapters ###

_HttpAdapters_ are responsible to get data from remote APIs.

Currently, there are the following adapters:

* `BuzzHttpAdapter` for [Buzz](https://github.com/kriswallsmith/Buzz), a lightweight PHP 5.3 library for issuing HTTP requests;
* `CurlHttpAdapter` for [cURL](http://php.net/manual/book.curl.php);
* `GuzzleHttpAdapter` for [Guzzle](https://github.com/guzzle/guzzle), PHP 5.3+ HTTP client and framework for building RESTful web service clients;
* `ZendHttpAdapter` for [Zend Http Client](http://framework.zend.com/manual/en/zend.http.client.html).


### Providers ###

_Providers_ contain the logic to extract useful information.

Currently, there are many providers for the following APIs:

* [FreeGeoIp](http://freegeoip.net/static/index.html) as IP-Based geocoding provider;
* [HostIp](http://www.hostip.info/) as IP-Based geocoding provider;
* [IpInfoDB](http://www.ipinfodb.com/) as IP-Based geocoding provider;
* [Yahoo! PlaceFinder](http://developer.yahoo.com/geo/placefinder/) as Address-Based geocoding and reverse geocoding provider;
* [Google Maps](http://code.google.com/apis/maps/documentation/geocoding/) as Address-Based geocoding and reverse geocoding provider;
* [Bing Maps](http://msdn.microsoft.com/en-us/library/ff701715.aspx) as Address-Based geocoding and reverse geocoding provider.


Installation
------------

If you don't use a _ClassLoader_ in your application, just require the provided autoloader:

``` php
<?php

require_once 'src/autoload.php';
```

You're done.


Usage
-----

First, you need an `adapter` to query an API:

``` php
<?php

$adapter  = new \Geocoder\HttpAdapter\BuzzHttpAdapter();
```

The `BuzzHttpAdapter` is tweakable, actually you can pass a `Browser` object to this adapter:

``` php
<?php

$buzz    = new \Buzz\Browser(new \Buzz\Client\Curl());
$adapter = new \Geocoder\HttpAdapter\BuzzHttpAdapter($buzz);
```

Now, you have to choose a `provider` which is closed to what you want to get.


### FreeGeoIpProvider ###

The `FreeGeoIpProvider` is able to geocode **IP addresses** only.


### HostIpProvider ###

The `HostIpProvider` is able to geocode **IP addresses** only.


### IpInfoDbProvider ###

The `IpInfoDbProvider` is able to geocode **IP addresses** only.


### YahooProvider ###

The `YahooProvider` is able to geocode both **IP addresses** and **street addresses**.
This provider can also reverse information based on coordinates (latitude, longitude).

### GoogleMapsProvider ###

The `GoogleMapsProvider` is able to geocode and reverse geocode **street addresses**.

### BingMapsProvider ###

The `BingMapsProvider` is able to geocode and reverse geocode **street addresses**.


You can use one of them or write your own provider. You can also register all providers and decide later.
That's we'll do:

``` php
<?php

$geocoder = new \Geocoder\Geocoder();
$geocoder->registerProviders(array(
    new \Geocoder\Provider\YahooProvider(
        $adapter, '<YAHOO_API_KEY>', $locale
    ),
    new \Geocoder\Provider\IpInfoDbProvider(
        $adapter, '<IPINFODB_API_KEY>'
    ),
    new \Geocoder\Provider\HostIpProvider($adapter)
));
```

The `$locale` parameter is available for the `YahooProvider`.

Everything is ok, enjoy!

API
---

The main method is called `geocode()` which receives a value to geocode. It can be an IP address or a street address (partial or not).

``` php
<?php

$result = $geocoder->geocode('88.188.221.14');
// Result is:
// "latitude"   => string(9) "47.901428"
// "longitude"  => string(8) "1.904960"
// "city"       => string(7) "Orleans"
// "zipcode"    => string(0) ""
// "region"     => string(6) "Centre"
// "country"    => string(6) "France"

$result = $geocoder->geocode('10 rue Gambetta, Paris, France');
// Result is:
// "latitude"   => string(9) "48.863217"
// "longitude"  => string(8) "2.388821"
// "city"       => string(5) "Paris"
// "zipcode"    => string(5) "75020"
// "region"     => string(14) "Ile-de-France"
// "country"    => string(6) "France"
```

The `geocode()` method returns a `Geocoded` result object with the following API, this object also implements the `ArrayAccess` interface:

* `getCoordinates()` will return an array with `latitude` and `longitude` values;
* `getLatitude()` will return the `latitude` value;
* `getLongitude()` will return the `longitude` value;
* `getCity()` will return the `city`;
* `getZipcode()` will return the `zipcode`;
* `getRegion()` will return the `region`;
* `getCountry()` will return te `country`.

The Geocoder's API is fluent, you can write:

``` php
<?php

$result = $geocoder
    ->registerProvider(new \My\Provider\Custom($adapter))
    ->using('custom')
    ->geocode('68.145.37.34')
    ;
```

The `using()` method allows you to choose the `adapter` to use. When you deal with multiple adapters, you may want to
choose one of them. The default behavior is to use the first one but it can be annoying.


Reverse Geocoding
-----------------

This library provides a `reverse()` method to retrieve information from coordinates:

``` php
$result = $geocoder->reverse($latitude, $longitude);
```

**Note:** the `YahooProvider` bundled in this lib is the unique provider able to do this feature.


Cache
-----

You can add a **cache layer** to the `Geocoder` object in order to save API calls by using the method `registerCache()` or passing
a cache object as a second argument of the `Geocoder` constructor. The cache object must implement the `CacheInterface` interface.

There is only one cache layer provided at the moment: `InMemory` which is used for unit tests and provided as an example.


Extending Things
----------------

You can provide your own `adapter`, you just need to create a new class which implements `HttpAdapterInterface`.

You can also write your own `provider` by implementing the `ProviderInterface`.

Note, the `AbstractProvider` class can help you by providing useful features.

Finally, you can write your own `cache` layer by implementing the `CacheInterface`.


Unit Tests
----------

To run unit tests, you'll need a set of dependencies you can install by running the `install_vendors.sh` script:

```
./bin/install_vendors.sh
```

Once installed, just launch the following command:

```
phpunit
```

You'll obtain some _skipped_ unit tests due to the need of API keys.

Rename the `phpunit.xml.dist` file to `phpunit.xml`, then uncomment the following lines and add your own API keys:

``` xml
<php>
    <!-- <server name="IPINFODB_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="YAHOO_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="GOOGLEMAPS_API_KEY" value="YOUR_API_KEY" /> -->
</php>
```

You're done.


Credits
-------

* William Durand <william.durand1@gmail.com>
* [All contributors](https://github.com/willdurand/Geocoder/contributors)


License
-------

Geocoder is released under the MIT License. See the bundled LICENSE file for details.
