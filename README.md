Geocoder
========

**Geocoder** is a library which helps you build geo-aware applications. It provides an abstraction layer for geocoding manipulations.
The library is splitted in two parts: `HttpAdapter` and `Provider` and is really extensible.

[![Build Status](https://secure.travis-ci.org/willdurand/Geocoder.png)](http://travis-ci.org/willdurand/Geocoder)


### HttpAdapters ###

_HttpAdapters_ are responsible to get data from remote APIs.

Currently, there are the following adapters:

* `BuzzHttpAdapter` to use [Buzz](https://github.com/kriswallsmith/Buzz), a lightweight PHP 5.3 library for issuing HTTP requests;
* `CurlHttpAdapter` to use [cURL](http://php.net/manual/book.curl.php);
* `GuzzleHttpAdapter` to use [Guzzle](https://github.com/guzzle/guzzle), PHP 5.3+ HTTP client and framework for building RESTful web service clients;
* `SocketHttpAdapter` to use a [socket](http://www.php.net/manual/function.fsockopen.php);
* `ZendHttpAdapter` to use [Zend Http Client](http://framework.zend.com/manual/2.0/en/modules/zend.http.client.html).


### Providers ###

_Providers_ contain the logic to extract useful information.

Currently, there are many providers for the following APIs:

* [FreeGeoIp](http://freegeoip.net/static/index.html) as IP-Based geocoding provider;
* [HostIp](http://www.hostip.info/) as IP-Based geocoding provider;
* [IpInfoDB](http://www.ipinfodb.com/) as IP-Based geocoding provider;
* [Yahoo! PlaceFinder](http://developer.yahoo.com/geo/placefinder/) as Address-Based geocoding and reverse geocoding provider;
* [Google Maps](http://code.google.com/apis/maps/documentation/geocoding/) as Address-Based geocoding and reverse geocoding provider;
* [Google Maps for Business](https://developers.google.com/maps/documentation/business/webservices) as Address-Based geocoding and reverse geocoding provider;
* [Bing Maps](http://msdn.microsoft.com/en-us/library/ff701715.aspx) as Address-Based geocoding and reverse geocoding provider;
* [OpenStreetMaps](http://nominatim.openstreetmap.org/) as Address-Based geocoding and reverse geocoding provider;
* [CloudMade](http://developers.cloudmade.com/projects/show/geocoding-http-api) as Address-Based geocoding and reverse geocoding provider;
* [Geoip](http://php.net/manual/book.geoip.php), the PHP extension, as IP-Based geocoding provider;
* ChainProvider is a special provider that takes a list of providers and iterates
  over this list to get information;
* [MapQuest](http://open.mapquestapi.com/) as Address-Based geocoding and reverse geocoding provider;
* [OIORest](http://geo.oiorest.dk/) as very accurate Address-Based geocoding and reverse geocoding provider (exclusively in Denmark);
* [GeoCoder.ca](http://geocoder.ca/) as Address-Based geocoding and reverse geocoding provider (exclusively in USA & Canada);
* [GeoCoder.us](http://geocoder.us/) as Address-Based geocoding provider (exclusively in USA);
* [IGN OpenLS](http://www.ign.fr/) as Address-Based geocoding provider (exclusively in France);
* [DataScienceToolkit](http://www.datasciencetoolkit.org/) as IP-Based geocoding provider;
* [Yandex](http://api.yandex.com.tr/maps/doc/geocoder/desc/concepts/About.xml) as Address-Based geocoding and reverse geocoding provider;
* [GeoPlugin](http://www.geoplugin.com/webservices) as IP-Based geocoding provider;
* [GeoIPs](http://www.geoips.com/developer/geoips-api) as IP-Based geocoding provider;
* [MaxMind web service](http://dev.maxmind.com/geoip/web-services) as IP-Based geocoding provider.


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

If you don't use either **Composer** or a _ClassLoader_ in your application, just require the provided autoloader:

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

The `FreeGeoIpProvider` is able to geocode **IPv4 and IPv6 addresses** only.


### HostIpProvider ###

The `HostIpProvider` is able to geocode **IPv4 addresses** only.


### IpInfoDbProvider ###

The `IpInfoDbProvider` is able to geocode **IPv4 addresses** only.
A valid api key is required.


### YahooProvider ###

The `YahooProvider` is able to geocode both **IPv4 addresses** and **street addresses**.
This provider can also reverse information based on coordinates (latitude, longitude).
A valid api key is required.


### GoogleMapsProvider ###

The `GoogleMapsProvider` is able to geocode and reverse geocode **street addresses**.


### BingMapsProvider ###

The `BingMapsProvider` is able to geocode and reverse geocode **street addresses**.
A valid api key is required.


### OpenStreetMapsProvider ###

The `OpenStreetMapsProvider` is able to geocode and reverse geocode **street addresses**.


### CloudMadeProvider ###

The `CloudMadeProvider` is able to geocode and reverse geocode **street addresses**.
A valid api key is required.


### GeoipProvider ###

The `GeoipProvider` is able to geocode **IPv4 and IPv6 addresses** only. No need to use an `HttpAdapter` as it uses a local database.
See the [MaxMind page](http://www.maxmind.com/app/php) for more information.


### ChainProvider ###

The `ChainProvider` is a special provider that takes a list of providers and iterates over this list to get information.


### MapQuestProvider ###

The `MapQuestProvider` is able to geocode and reverse geocode **street addresses**.


### OIORestProvider ###

The `OIORestProvider` is able to geocode and reverse geocode **street addresses**, exclusively in Denmark.


### GeocoderCaProvider ###

The `GeocoderCaProvider` is able to geocode and reverse geocode **street addresses**, exclusively in USA & Canada.


### GeocoderUsProvider ###

The `GeocoderUsProvider` is able to geocode **street addresses** only, exclusively in USA.


### IGNOpenLSProvider ###

The `IGNOpenLSProvider` is able to geocode **street addresses** only, exclusively in France.
A valid api key is required.


### DataScienceToolkitProvider ###

The `DataScienceToolkitProvider` is able to geocode **IPv4 addresses** only.


### YandexProvider ###

The `YandexProvider` is able to geocode and reverse geocode **street addresses**.
The default langage-locale is `ru-RU`, you can choose between `uk-UA`, `be-BY`,
`en-US`, `en-BR` and `tr-TR`.
This provider can also reverse information based on coordinates (latitude,
longitude). It's possible to precise the toponym to get more accurate result:
`house`, `street`, `metro`, `district` and `locality`.


### GeoPluginProvider ###

The `GeoPluginProvider` is able to geocode **IPv4 addresses and IPv6 addresses** only.


### GeoIPsProvider ###

The `GeoIPsProvider` is able to geocode **IPv4 addresses** only.
A valid api key is required.


### MaxMindProvider ###

The `MaxMindProvider` is able to geocode **IPv4 addresses** only.
A valid api key is required.


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
// "cityDistrict"   => string(0) ""
// "city"           => string(7) "Orleans"
// "zipcode"        => string(0) ""
// "county"         => string(6) "Loiret"
// "countyCode"     => null
// "region"         => string(6) "Centre"
// "regionCode"     => null
// "country"        => string(6) "France"
// "countryCode"    => string(2) "FR"
// "timezone"       => string(6) "Europe/Paris"

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
// "cityDistrict"   => string(18) "20E Arrondissement"
// "city"           => string(5) "Paris"
// "county"         => string(5) "Paris"
// "countyCode"     => null
// "zipcode"        => string(5) "75020"
// "region"         => string(14) "Ile-de-France"
// "regionCode"     => null
// "country"        => string(6) "France"
// "countryCode"    => string(2) "FR"
// "timezone"       => string(6) "Europe/Paris"
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
* `getCityDistrict()` will return the `city district`, or `sublocality`;
* `getCounty()` will return the `county`;
* `getCountyCode()` will return the `county` code (county short name);
* `getRegion()` will return the `region`;
* `getRegionCode()` will return the `region` code (region short name);
* `getCountry()` will return the `country`;
* `getCountryCode()` will return the ISO `country` code;
* `getTimezone()` will return the `timezone`.

The Geocoder's API is fluent, you can write:

``` php
<?php

$result = $geocoder
    ->registerProvider(new \My\Provider\Custom($adapter))
    ->using('custom')
    ->geocode('68.145.37.34')
    ;
```

The `using()` method allows you to choose the `provider` to use. When you deal with multiple providers, you may want to
choose one of them. The default behavior is to use the first one but it can be annoying.


Reverse Geocoding
-----------------

This library provides a `reverse()` method to retrieve information from coordinates:

``` php
$result = $geocoder->reverse($latitude, $longitude);
```


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

* County Code: `%p`

* Region: `%R`

* Region Code: `%r`

* Country: `%C`

* Country Code: `%c`

* Timezone: `%T`


Extending Things
----------------

You can provide your own `adapter`, you just need to create a new class which implements `HttpAdapterInterface`.

You can also write your own `provider` by implementing the `ProviderInterface`.

Note, the `AbstractProvider` class can help you by providing useful features.

You can provide your own `dumper` by implementing the `DumperInterface`.

Write your own `formatter` by implementing the `FormatterInterface`.


Unit Tests
----------

To run unit tests, you'll need `cURL` and a set of dependencies you can install using Composer:

```
php composer.phar install --dev
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
    <!-- <server name="IGN_WEB_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="GEOIPS_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="MAXMIND_API_KEY" value="YOUR_API_KEY" /> -->
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
