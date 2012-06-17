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
* [Bing Maps](http://msdn.microsoft.com/en-us/library/ff701715.aspx) as Address-Based geocoding and reverse geocoding provider;
* [OpenStreetMaps](http://nominatim.openstreetmap.org/) as Address-Based geocoding and reverse geocoding provider;
* [CloudMade](http://developers.cloudmade.com/projects/show/geocoding-http-api) as Address-Based geocoding and reverse geocoding provider;
* [Geoip](http://php.net/manual/book.geoip.php), the PHP extension, as IP-Based geocoding provider.


Installation
------------

The recommended way to install Geocoder is through composer.

Just create a `composer.json` file for your project:

``` json
{
    "require": {
        "willdurand/geocoder": "*"
    }
}
```

And run these two commands to install it:

``` bash
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install
```

Now you can add the autoloader, and you will have access to the library:

``` php
<?php

require 'vendor/autoload.php';
```

If you don't use neither **Composer** nor a _ClassLoader_ in your application, just require the provided autoloader:

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


### OpenStreetMapsProvider ###

The `OpenStreetMapsProvider` is able to geocode and reverse geocode **street addresses**.


### CloudMadeProvider ###

The `CloudMadeProvider` is able to geocode and reverse geocode **street addresses**.


### GeoipProvider ###

The `GeoipProvider` is able to geocode **IP addresses** only. No need to use an `HttpAdapter` as it uses a local database.
See the [MaxMind page](http://www.maxmind.com/app/php) for more information.


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
// "latitude"       => string(9) "47.901428"
// "longitude"      => string(8) "1.904960"
// "bounds"         => array(4) {
//     "south" => string(9) "47.813320"
//     "west"  => string(8) "1.809770"
//     "north" => string(9) "47.960220"
//     "east"  => string(8) "1.993860"
// }
// "streetNumber"   => string(0) ""
// "streetName"     => string(0) ""
// "city"           => string(7) "Orleans"
// "zipcode"        => string(0) ""
// "county"         => string(6) "Loiret"
// "region"         => string(6) "Centre"
// "country"        => string(6) "France"

$result = $geocoder->geocode('10 rue Gambetta, Paris, France');
// Result is:
// "latitude"       => string(9) "48.863217"
// "longitude"      => string(8) "2.388821"
// "bounds"         => array(4) {
//     "south" => string(9) "48.863217"
//     "west"  => string(8) "2.388821"
//     "north" => string(9) "48.863217"
//     "east"  => string(8) "2.388821"
// }
// "streetNumber"   => string(2) "10"
// "streetName"     => string(15) "Avenue Gambetta"
// "city"           => string(5) "Paris"
// "county"         => string(5) "Paris"
// "zipcode"        => string(5) "75020"
// "region"         => string(14) "Ile-de-France"
// "country"        => string(6) "France"
```

The `geocode()` method returns a `Geocoded` result object with the following API, this object also implements the `ArrayAccess` interface:

* `getCoordinates()` will return an array with `latitude` and `longitude` values;
* `getLatitude()` will return the `latitude` value;
* `getLongitude()` will return the `longitude` value;
* `getBounds()` will return an array with `south`, `west`, `north` and `east` values;
* `getStreetNumber()` will return the `street number/house number` value;
* `getStreetName()` will return the `street name` value;
* `getCity()` will return the `city`;
* `getZipcode()` will return the `zipcode`;
* `getCounty()` will return the `county`;
* `getRegion()` will return the `region`;
* `getRegionCode()` will return the `region` code (region short name);
* `getCountry()` will return the `country`;
* `getCountryCode()` will return the ISO country code.

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


Dumpers
-------

**Geocoder** provides dumpers that aim to transform a `ResultInterface` object in standard formats.

### GPS eXchange Format (GPX) ###

The **GPS eXchange** format is designed to share geolocated data like point of interests, tracks, ways, but also
coordinates. **Geocoder** provides a dumper to convert a `ResultInterface` object in an GPX compliant format.

Assuming we got a `$result` object as seen previously:

``` php
<?php

$dumper = new \Geocoder\Dumper\GpxDumper();
$strGpx = $dumper->dump($result);

echo $strGpx;
```

It will display:

``` xml
<gpx
    version="1.0"
    creator="Geocoder" version="1.0.1-dev"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/0"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">
    <bounds minlat="2.388911" minlon="48.863151" maxlat="2.388911" maxlon="48.863151"/>
    <wpt lat="48.8631507" lon="2.3889114">
        <name><![CDATA[Paris]]></name>
        <type><![CDATA[Address]]></type>
    </wpt>
</gpx>
```

### GeoJSON ###

[GeoJSON](http://geojson.org/) is a format for encoding a variety of geographic data structures.


### Keyhole Markup Language (KML) ###

[Keyhole Markup Language](http://en.wikipedia.org/wiki/Keyhole_Markup_Language) is an XML notation
for expressing geographic annotation and visualization within Internet-based, two-dimensional maps
and three-dimensional Earth browsers.


### Well-Known Binary (WKB) ###

The Well-Known Binary (WKB) representation for geometric values is defined by the OpenGIS specification.


### Well-Known Text (WKT) ###

Well-known text (WKT) is a text markup language for representing vector geometry objects on a map,
spatial reference systems of spatial objects and transformations between spatial reference systems.


Formatter
---------

A common use case is to print geocoded data. Thanks to the `Formatter` class,
it's really easy to format a `ResultInterface` object as a string:

``` php
<?php

// $result is an instance of ResultInterface
$formatter = new \Geocoder\Formatter\Formatter($result);

$formatter->format('%S %n, %z %L');
// 'Badenerstrasse 120, 8001 Zuerich'

$formatter->format('<p>%S %n, %z %L</p>');
// '<p>Badenerstrasse 120, 8001 Zuerich</p>'
```

Here is the mapping:

* Street Number: `%n`

* Street Name: `%S`

* City: `%L`

* Zipcode: `%z`

* County: `%P`

* Region: `%R`

* Region Code: `%r`

* Country: `%C`

* Country Code: `%c`


Extending Things
----------------

You can provide your own `adapter`, you just need to create a new class which implements `HttpAdapterInterface`.

You can also write your own `provider` by implementing the `ProviderInterface`.

Note, the `AbstractProvider` class can help you by providing useful features.

You can provide your own `dumper` by implementing the `DumperInterface`.

Write your own `formatter` by implementing the `FormatterInterface`.


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
    <!-- <server name="BINGMAPS_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="CLOUDMADE_API_KEY" value="YOUR_API_KEY" /> -->
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
