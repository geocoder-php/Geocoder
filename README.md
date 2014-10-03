Geocoder
========

**Geocoder** is a library which helps you build geo-aware applications. It provides an abstraction layer for geocoding manipulations.
The library is split in two parts: `HttpAdapter` and `Provider` and is really extensible.

[![Build Status](https://secure.travis-ci.org/geocoder-php/Geocoder.png)](http://travis-ci.org/geocoder-php/Geocoder)


### HttpAdapters ###

_HttpAdapters_ are responsible to get data from remote APIs.

Currently, there are the following adapters:

* `BuzzHttpAdapter` to use [Buzz](https://github.com/kriswallsmith/Buzz), a lightweight PHP 5.3 library for issuing HTTP requests;
* `CurlHttpAdapter` to use [cURL](http://php.net/manual/book.curl.php);
* `GuzzleHttpAdapter` to use [Guzzle](https://github.com/guzzle/guzzle), PHP 5.3+ HTTP client and framework for building RESTful web service clients;
* `SocketHttpAdapter` to use a [socket](http://www.php.net/manual/function.fsockopen.php);
* `ZendHttpAdapter` to use [Zend Http Client](http://framework.zend.com/manual/2.0/en/modules/zend.http.client.html);
* `GeoIP2Adapter` to use [GeoIP2 Database Reader](https://github.com/maxmind/GeoIP2-php#database-reader) or the [Webservice Client](https://github.com/maxmind/GeoIP2-php#web-service-client) by MaxMind.


### Providers ###

_Providers_ contain the logic to extract useful information.

Currently, there are many providers for the following APIs:

Address-based geocoding

provider      | reverse | SSL | coverage | terms
:------------- |:--------- |:--------- |:--------- |:-----
[Google Maps](https://developers.google.com/maps/documentation/geocoding/) | yes | no | worldwide | requires API key. Limit 2500 requests per day
[Google Maps for Business](https://developers.google.com/maps/documentation/business/) | yes | no | worldwide | requires API key. Limit 100,000 requests per day
[Bing Maps](http://msdn.microsoft.com/en-us/library/ff701713.aspx) | yes | no | worldwide | requires API key. Limit 10,000 requests per month.
[OpenStreetMap](http://wiki.openstreetmap.org/wiki/Nominatim) | yes | no | worldwide | heavy users (>1q/s) get banned
Nominatim    | yes | supported | worldwide | requires a domain name (e.g. local installation)
[MapQuest](http://developer.mapquest.com/web/products/dev-services/geocoding-ws)  | yes | no | worldwide | both open and [commercial service](http://platform.mapquest.com/geocoding/) require API key
[OpenCage](http://geocoder.opencagedata.com/)  | yes | supported | worldwide | requires API key. 2500 requests/day free
[Yandex](http://api.yandex.com/maps/)  | yes | no | worldwide
[Geonames](http://www.geonames.org/commercial-webservices.html)  | yes |no | worldwide | requires registration, no free tier
[TomTom](https://geocoder.tomtom.com/app/view/index)  | yes | required | worldwide | requires API key. First 2500 requests or 30 days free
[ArcGIS Online](https://developers.arcgis.com/en/features/geocoding/) | yes | supported | worldwide | requires API key. 1250 requests free
ChainProvider | | | | meta provider which iterates over a list of providers


IP-based geocoding

provider      | IPv6 | terms | notes
:------------- |:--------- |:--------- |:---------
[FreeGeoIp](http://freegeoip.net/) | yes
[HostIp](http://www.hostip.info/use.html) | no
[IpInfoDB](http://ipinfodb.com/) | no | city precision
Geoip| ? | | wrapper around the [PHP extension](http://php.net/manual/en/book.geoip.php)
[GeoPlugin](http://www.geoplugin.com/) | yes
[GeoIPs](http://www.geoips.com/en/) | no | requires API key
[MaxMind](https://www.maxmind.com/) web service | yes | requires Omni API key | City/ISP/Org and Omni services, IPv6 on country level
MaxMind binary file | yes | | needs locally installed database files
MaxMind [GeoIP2](https://www.maxmind.com/en/geoip2-databases) | yes |

The [Geocoder Extra](https://github.com/geocoder-php/geocoder-extra) library contains even more providers!


Installation
------------

The recommended way to install Geocoder is through composer.

Just create a `composer.json` file for your project:

``` json
{
    "require": {
        "willdurand/geocoder": "@stable"
    }
}
```

**Protip:** you should browse the [`willdurand/geocoder`](https://packagist.org/packages/willdurand/geocoder)
page to choose a stable version to use, avoid the `@stable` meta constraint.

And run these two commands to install it:

``` bash
$ curl -sS https://getcomposer.org/installer | php
$ composer install
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

The `FreeGeoIpProvider` named `free_geo_ip` is able to geocode **IPv4 and IPv6 addresses** only.


### HostIpProvider ###

The `HostIpProvider` named `host_ip` is able to geocode **IPv4 addresses** only.


### IpInfoDbProvider ###

The `IpInfoDbProvider` named `ip_info_db` is able to geocode **IPv4 addresses** only.
A valid api key is required.


### GoogleMapsProvider ###

The `GoogleMapsProvider` named `google_maps` is able to geocode and reverse geocode **street addresses**.
A locale and a region can be set as well as an optional api key. This provider also supports SSL.


### GoogleMapsBusinessProvider ###

The `GoogleMapsBusinessProvider` named `google_maps_business` is able to geocode and reverse geocode **street addresses**.
A valid `Client ID` is required. The private key is optional. This provider also supports SSL.


### BingMapsProvider ###

The `BingMapsProvider` named `bing_maps` is able to geocode and reverse geocode **street addresses**.
A valid api key is required.


### OpenStreetMapProvider ###

The `OpenStreetMapProvider` named `openstreetmap` is able to geocode and reverse
geocode **street addresses**.


### NominatimProvider ###

The `NominatimProvider` named `nominatim` is able to geocode and reverse geocode **street addresses**.
Access to a Nominatim server is required. See the [Nominatim
Wiki Page](http://wiki.openstreetmap.org/wiki/Nominatim) for more information.


### GeoipProvider ###

The `GeoipProvider` named `geoip` is able to geocode **IPv4 and IPv6 addresses** only. No need to use an `HttpAdapter` as it uses a local database.
See the [MaxMind page](http://www.maxmind.com/app/php) for more information.


### ChainProvider ###

The `ChainProvider` named `chain` is a special provider that takes a list of providers and iterates over this list to get information.


### MapQuestProvider ###

The `MapQuestProvider` named `map_quest` is able to geocode and reverse geocode **street addresses**.
A valid api key is required. Access to [MapQuest's licensed endpoints](http://developer.mapquest.com/web/tools/getting-started/platform/licensed-vs-open)
is provided via constructor argument.


### OpenCageProvider ###

The `OpenCageProvider` named `opencage` is able to geocode and reverse geocode **street addresses**.
A valid api key is required.


### YandexProvider ###

The `YandexProvider` named `yandex` is able to geocode and reverse geocode **street addresses**.
The default language-locale is `ru-RU`, you can choose between `uk-UA`, `be-BY`,
`en-US`, `en-BR` and `tr-TR`.
This provider can also reverse information based on coordinates (latitude,
longitude). It's possible to precise the toponym to get more accurate result for reverse geocoding:
`house`, `street`, `metro`, `district` and `locality`.


### GeoPluginProvider ###

The `GeoPluginProvider` named `geo_plugin` is able to geocode **IPv4 addresses and IPv6 addresses** only.


### GeoIPsProvider ###

The `GeoIPsProvider` named `geo_ips` is able to geocode **IPv4 addresses** only.
A valid api key is required.


### MaxMindProvider ###

The `MaxMindProvider` named `maxmind` is able to geocode **IPv4 and IPv6 addresses** only.
A valid `City/ISP/Org` or `Omni` service's api key is required.
This provider provides two constants `CITY_EXTENDED_SERVICE` by default and `OMNI_SERVICE`.


### MaxMindBinaryProvider ###

The `MaxMindBinaryProvider` named `maxmind_binary` is able to geocode **IPv4 and IPv6 addresses**
only. It requires a data file, and the [geoip/geoip](https://packagist.org/packages/geoip/geoip)
package must be installed.

It is worth mentioning that this provider has **serious performance issues**, and should **not**
be used in production. For more information, please read [issue #301](https://github.com/geocoder-php/Geocoder/issues/301).

### GeoIP2DatabaseProvider ###

The `GeoIP2Provider` named `maxmind_geoip2` is able to geocode **IPv4 and IPv6
addresses** only - it makes use of the MaxMind GeoIP2 databases or the
webservice.

It requires either the [database
file](http://dev.maxmind.com/geoip/geoip2/geolite2/), or the
[webservice](http://dev.maxmind.com/geoip/geoip2/web-services/) - represented by
the GeoIP2 Provider, which is injected to the `GeoIP2Adapter`. The
[geoip2/geoip2](https://packagist.org/packages/geoip2/geoip2) package must be
installed.

This provider will only work with the corresponding `GeoIP2Adapter`.

##### Usage

``` php
<?php

// Maxmind GeoIP2 Provider: e.g. the database reader
$reader   = new \GeoIp2\Database\Reader('/path/to/database');

$adapter  = new \Geocoder\HttpAdapter\GeoIP2Adapter($reader);
$provider = new \Geocoder\Provider\GeoIP2Provider($adapter);
$geocoder = new \Geocoder\Geocoder($provider);

$result   = $geocoder->geocode('74.200.247.59');
```

### GeonamesProvider ###

The `GeonamesProvider` named `geonames` is able to geocode and reverse geocode **places**.
A valid username is required.

### TomTomProvider ###

The `TomTomProvider` named `tomtom` is able to geocode and reverse geocode **street addresses**.
The default langage-locale is `en`, you can choose between `de`, `es`, `fr`, `it`, `nl`, `pl`, `pt` and `sv`.
A valid api key is required.

### ArcGISOnlineProvider ###

The `ArcGISOnlineProvider` named `arcgis_online` is able to geocode and reverse geocode **street addresses**.
It's possible to specify a sourceCountry to restrict result to this specific country thus reducing
request time (note that this doesn't work on reverse geocoding). This provider also supports SSL.


### Using The Providers ###

You can use one of them or write your own provider. You can also register all providers and decide later.
That's we'll do:

``` php
<?php

$geocoder = new \Geocoder\Geocoder();
$geocoder->registerProviders(array(
    new \Geocoder\Provider\GoogleMapsProvider(
        $adapter, $locale, $region, $useSsl
    ),
    new \Geocoder\Provider\GoogleMapsBusinessProvider(
        $adapter, '<CLIENT_ID>', '<PRIVATE_KEY>', $locale, $region, $useSsl
    ),
    new \Geocoder\Provider\YandexProvider(
        $adapter, $locale, $toponym
    ),
    new \Geocoder\Provider\MaxMindProvider(
        $adapter, '<MAXMIND_API_KEY>', $service, $useSsl
    ),
    new \Geocoder\Provider\ArcGISOnlineProvider(
        $adapter, $sourceCountry, $useSsl
    ),
    new \Geocoder\Provider\NominatimProvider(
        $adapter, 'http://your.nominatim.server', $locale
    ),
));
```

Parameters:

* `$locale` is available for `YandexProvider`, `BingMapsProvider`, `OpenCageProvider` and `TomTomProvider`.
* `$region` is available for `GoogleMapsProvider` and `GoogleMapsBusinessProvider`.
* `$toponym` is available for `YandexProvider`.
* `$service` is available for `MaxMindProvider`.
* `$useSsl` is available for `GoogleMapsProvider`, `GoogleMapsBusinessProvider`, `OpenCageProvider`, `MaxMindProvider` and `ArcGISOnlineProvider`.
* `$sourceCountry` is available for `ArcGISOnlineProvider`.
* `$rootUrl` is available for `NominatimProvider`.

### Using The ChainProvider ###

As said it's a special provider that takes a list of providers and iterates over this list to get information. Note
that it **stops** its iteration when a provider returns a result. The result is returned by `GoogleMapsProvider`
because `FreeGeoIpProvider` and `HostIpProvider` cannot geocode street addresses. `BingMapsProvider` is ignored.

``` php
$geocoder = new \Geocoder\Geocoder();
$adapter  = new \Geocoder\HttpAdapter\CurlHttpAdapter();
$chain    = new \Geocoder\Provider\ChainProvider(array(
    new \Geocoder\Provider\FreeGeoIpProvider($adapter),
    new \Geocoder\Provider\HostIpProvider($adapter),
    new \Geocoder\Provider\GoogleMapsProvider($adapter, 'fr_FR', 'France', true),
    new \Geocoder\Provider\BingMapsProvider($adapter, '<API_KEY>'),
    // ...
));
$geocoder->registerProvider($chain);

try {
    $geocode = $geocoder->geocode('10 rue Gambetta, Paris, France');
    var_export($geocode);
} catch (Exception $e) {
    echo $e->getMessage();
}
```

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
    ->limit(10)
    ->geocode('68.145.37.34')
    ;
```

The `using()` method allows you to choose the `provider` to use by its name.
When you deal with multiple providers, you may want to choose one of them.
The default behavior is to use the first one but it can be annoying.

The `limit()` method allows you to configure the maximum number of results
being returned. Depending on the provider you may not get as many results as
expected, it is a maximum limit, not the expected number of results.


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

* City District: `%D`

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

You can provide your own `result` by extending `DefaultResultFactory` or `MultipleResultFactory` and implementing
`ResultInterface` if your provider returns one or multiple results and more informations than the default one.
Please note that the method `createFromArray` is marked `final` in these factories.

If you need your own `ResultFactory`, just implement `ResultFactoryInterface`.

Note, `AbstractProvider` and `AbstractResult` classes can help you by providing useful features.

You can provide your own `dumper` by implementing the `DumperInterface`.

Write your own `formatter` by implementing the `FormatterInterface`.


Contributing
------------

See CONTRIBUTING file.


Unit Tests
----------

To run unit tests, you'll need `cURL` and a set of dependencies you can install using Composer:

```
composer install --dev
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
    <!-- <server name="BINGMAPS_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="GEOIPS_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="MAXMIND_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="GEONAMES_USERNAME" value="YOUR_USERNAME" /> -->
    <!-- <server name="TOMTOM_GEOCODING_KEY" value="YOUR_GEOCODING_KEY" /> -->
    <!-- <server name="TOMTOM_MAP_KEY" value="YOUR_MAP_KEY" /> -->
    <!-- <server name="GOOGLE_GEOCODING_KEY" value="YOUR_GEOCODING_KEY" /> -->
    <!-- <server name="OPENCAGE_API_KEY" value="YOUR_API_KEY" /> -->
</php>
```

You're done.


Credits
-------

* William Durand <william.durand1@gmail.com>
* [All contributors](https://github.com/geocoder-php/Geocoder/contributors)


License
-------

Geocoder is released under the MIT License. See the bundled LICENSE file for details.
