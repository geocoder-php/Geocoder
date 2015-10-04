Geocoder
========

[![Build
Status](https://secure.travis-ci.org/geocoder-php/Geocoder.png)](http://travis-ci.org/geocoder-php/Geocoder)
[![Total
Downloads](https://poser.pugx.org/willdurand/Geocoder/downloads.png)](https://packagist.org/packages/willdurand/Geocoder)
[![Latest Stable
Version](https://poser.pugx.org/willdurand/Geocoder/v/stable.png)](https://packagist.org/packages/willdurand/Geocoder)

> **Important:** You are browsing the documentation of Geocoder **3.x**.
Documentation for version **2.x** is available here: [Geocoder 2.x
documentation](https://github.com/geocoder-php/Geocoder/blob/2.x/README.md).

---

**Geocoder** is a PHP library which helps you build geo-aware applications by
providing a powerful abstraction layer for geocoding manipulations.

* [Installation](#installation)
* [Usage](#usage)
  - [Address & AddressCollection](#address--addresscollection)
  - [The ProviderAggregator](#the-provideraggregator)
  - [TimedGeocoder](#timedgeocoder)
  - [HTTP Adapters](#http-adapters)
  - [Providers](#providers)
    - [Address-based Providers](#address-based-providers)
      - [ArcGISOnline](#arcgisonline)
      - [GeoIP2](#geoip2)
      - [GoogleMaps](#googlemaps)
      - [GoogleMapsBusiness](#googlemapsbusiness)
      - [MaxMindBinary](#maxmindbinary)
      - [Nominatim](#nominatim)
      - [TomTom](#tomtom)
      - [Yandex](#yandex)
    - [IP-based Providers](#ip-based-providers)
    - [Locale Aware Providers](#locale-aware-providers)
    - [The Chain Provider](#the-chain-provider)
  - [Dumpers](#dumpers)
    - [GPS eXchange Format (GPX)](#gps-exchange-format-gpx)
    - [GeoJSON](#geojson)
    - [Keyhole Markup Language (KML)](#keyhole-markup-language-kml)
    - [Well-Known Binary (WKB)](#well-known-binary-wkb)
    - [Well-Known Text (WKT)](#well-known-text-wkt)
  - [Formatters](#formatters)
* [Extending Things](#extending-things)
* [Versioning](#versioning)


Installation
------------

The recommended way to install Geocoder is through
[Composer](http://getcomposer.org):

```
$ composer require willdurand/geocoder
```


Usage
-----

[Geocoder](https://github.com/geocoder-php/Geocoder) and its companion
[Geocoder Extra](https://github.com/geocoder-php/geocoder-extra) provides a lot
of [providers](#providers).

Choose the one that fits your need first. Let's say the `GoogleMaps` one is what
you were looking for, so let's see how to use it. In the code snippet below,
`curl` has been chosen as [HTTP layer](#http-adapters) but it is up to you
since each HTTP-based provider implements
[PSR-7](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md).

```php
$curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
$geocoder = new \Geocoder\Provider\GoogleMaps($curl);

$geocoder->geocode(...);
$geocoder->reverse(...);
```

The `Geocoder` interface, which all providers implement, exposes two main
methods:

* `geocode($streetOrIpAddress)`
* `reverse($latitude, $longitude)`

It also contains methods to control the number of results:

* `limit($limit)`
* `getLimit()`

### Address & AddressCollection

Both `geocode()` and `reverse()` methods return a collection of `Address`
objects (`AddressCollection`), each providing the following API:

* `getCoordinates()` will return a `Coordinates` object (with `latitude` and
  `longitude` properties);
* `getLatitude()` will return the `latitude` value;
* `getLongitude()` will return the `longitude` value;
* `getBounds()` will return an `Bounds` object (with `south`, `west`, `north`
  and `east` properties);
* `getStreetNumber()` will return the `street number/house number` value;
* `getStreetName()` will return the `street name` value;
* `getLocality()` will return the `locality` or `city`;
* `getPostalCode()` will return the `postalCode` or `zipcode`;
* `getSubLocality()` will return the `city district`, or `sublocality`;
* `getAdminLevels()` will return an ordered collection (`AdminLevelCollection`)
  of `AdminLevel` object (with `level`, `name` and `code` properties);
* `getCountry()` will return a `Country` object (with `name` and `code`
  properties);
* `getCountryCode()` will return the ISO `country` code;
* `getTimezone()` will return the `timezone`.

The `AddressCollection` exposes the following methods:

* `count()` (this class implements `Countable`);
* `first()` retrieves the first `Address`;
* `slice($offset, $length = null)` returns `Address` objects between `$offset`
  and `length`;
* `get($index)` fetches an `Address` using its `$index`;
* `all()` returns all `Address` objects;
* `getIterator()` (this class implements `IteratorAggregate`).

### The ProviderAggregator

The `ProviderAggregator` is used to register several providers so that you can
decide which provider to use later on.

``` php
<?php

$geocoder = new \Geocoder\ProviderAggregator();

$geocoder->registerProviders([
    new \Geocoder\Provider\GoogleMaps(
        $adapter, $locale, $region, $useSsl
    ),
    new \Geocoder\Provider\GoogleMapsBusiness(
        $adapter, '<CLIENT_ID>', '<PRIVATE_KEY>', $locale, $region, $useSsl
    ),
    new \Geocoder\Provider\Yandex(
        $adapter, $locale, $toponym
    ),
    new \Geocoder\Provider\MaxMind(
        $adapter, '<MAXMIND_API_KEY>', $service, $useSsl
    ),
    new \Geocoder\Provider\ArcGISOnline(
        $adapter, $sourceCountry, $useSsl
    ),
]);

$geocoder->registerProvider(
    new \Geocoder\Provider\Nominatim(
        $adapter, 'http://your.nominatim.server', $locale
    )
);

$geocoder
    ->using('google_maps')
    ->geocode('...');

$geocoder
    ->limit(10)
    ->reverse($lat, $lng);
```

The `ProviderAggregator`'s API is fluent, meaning you can write:

``` php
<?php

$addresses = $geocoder
    ->registerProvider(new \My\Provider\Custom($adapter))
    ->using('custom')
    ->limit(10)
    ->geocode('68.145.37.34')
    ;
```

The `using()` method allows you to choose the `provider` to use by its name.
When you deal with multiple providers, you may want to choose one of them.  The
default behavior is to use the first one but it can be annoying.

The `limit()` method allows you to configure the maximum number of results being
returned. Depending on the provider you may not get as many results as expected,
it is a maximum limit, not the expected number of results.

### TimedGeocoder

The `TimedGeocoder` class profiles each `geocode` and `reverse` call. So you can
easily figure out how many time/memory was spent for each geocoder/reverse call.

```php
// configure you geocoder object

$stopwatch = new \Symfony\Component\Stopwatch\Stopwatch();
$geocoder = new \Geocoder\TimedGeocoder($geocoder, $stopwatch);

$geocoder->geocode('Paris, France');

// Now you can debug your application
```

We use the [symfony/stopwatch](http://symfony.com/doc/current/components/stopwatch.html)
component under the hood. Which means, if you use the Symfony framework the
geocoder calls will appear in your timeline section in the Web Profiler.

### HTTP Adapters

In order to talk to geocoding APIs, you need HTTP adapters. While it was part of
the library in Geocoder 1.x and 2.x, Geocoder 3.x and upper now relies on the
[PSR-7
Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md)
which defines how HTTP message should be implemented. Choose any library that
follows this PSR and implement the specified interfaces to use with Geocoder.

As making choices is rather hard, Geocoder ships with the
[egeloen/http-adapter](https://github.com/egeloen/ivory-http-adapter) library by
default, but it is up to you to choose a different implementation.

**Note:** not all providers are HTTP-based.

### Providers

Providers perform the geocoding black magic for you (talking to the APIs,
fetching results, dealing with errors, etc.) and are highly configurable.

#### Address-based Providers

Provider       | Name | Reverse? | SSL? | Coverage | Multiple? | Terms
:------------- |:---- |:-------- |:---- |:-------- |:--------- |:-----
[ArcGIS Online](https://developers.arcgis.com/en/features/geocoding/) | `arcgis_online` | yes | supported | worldwide | yes | requires API key. 1250 requests free
[Bing Maps](http://msdn.microsoft.com/en-us/library/ff701713.aspx) | `bing_maps` | yes | no | worldwide | yes | requires API key. Limit 10,000 requests per month
Chain | `chain` | | | | | meta provider which iterates over a list of providers
[Geonames](http://www.geonames.org/commercial-webservices.html) | `geonames` | yes |no | worldwide | yes | requires registration, no free tier
[Google Maps](https://developers.google.com/maps/documentation/geocoding/) | `google_maps` | yes | supported | worldwide | yes | requires API key. Limit 2500 requests per day
[Google Maps for Business](https://developers.google.com/maps/documentation/business/) | `google_maps_business` | yes | supported | worldwide | yes | requires API key. Limit 100,000 requests per day
[MapQuest](http://developer.mapquest.com/web/products/dev-services/geocoding-ws) | `map_quest` | yes | no | worldwide | yes | both open and [commercial service](http://platform.mapquest.com/geocoding/) require API key
[Nominatim](http://wiki.openstreetmap.org/wiki/Nominatim) | `nominatim` | yes | supported | worldwide | yes | requires a domain name (e.g. local installation)
[OpenCage](http://geocoder.opencagedata.com/) | `opencage` | yes | supported | worldwide | yes | requires API key. 2500 requests/day free
[OpenStreetMap](http://wiki.openstreetmap.org/wiki/Nominatim) | `openstreetmap` | yes | no | worldwide | yes | heavy users (>1q/s) get banned
[TomTom](https://geocoder.tomtom.com/app/view/index) | `tomtom` | yes | required | worldwide | yes | requires API key. First 2500 requests or 30 days free
[Yandex](http://api.yandex.com/maps/) | `yandex` | yes | no | worldwide | yes

Below, you will find more information for these providers.

##### ArcGISOnline

It is possible to specify a `sourceCountry` to restrict result to this specific
country thus reducing request time (note that this doesn't work on reverse
geocoding).

##### GeoIP2

It requires either the [database
file](http://dev.maxmind.com/geoip/geoip2/geolite2/), or the
[webservice](http://dev.maxmind.com/geoip/geoip2/web-services/) - represented by
the GeoIP2 , which is injected to the `GeoIP2Adapter`. The
[geoip2/geoip2](https://packagist.org/packages/geoip2/geoip2) package must be
installed.

This provider will only work with the corresponding `GeoIP2Adapter`:

``` php
<?php

// Maxmind GeoIP2 Provider: e.g. the database reader
$reader   = new \GeoIp2\Database\Reader('/path/to/database');

$adapter  = new \Geocoder\Adapter\GeoIP2Adapter($reader);
$geocoder = new \Geocoder\Provider\GeoIP2($adapter);

$address   = $geocoder->geocode('74.200.247.59')->first();
```

##### GoogleMaps

Locale and/or region can be specified:

```php
$geocoder = new \Geocoder\Provider\GoogleMaps(
    $httpAdapter,
    $locale,
    $region,
    $useSsl, // true|false
    $apiKey
);
```

##### GoogleMapsBusiness

A valid `Client ID` is required. The private key is optional. This provider also
supports SSL, and extends the `GoogleMaps` provider.

##### MaxMindBinary

This provider requires a data file, and the
[geoip/geoip](https://packagist.org/packages/geoip/geoip) package must be
installed.

It is worth mentioning that this provider has **serious performance issues**,
and should **not** be used in production. For more information, please read
[issue #301](https://github.com/geocoder-php/Geocoder/issues/301).

##### Nominatim

Access to a Nominatim server is required. See the [Nominatim Wiki
Page](http://wiki.openstreetmap.org/wiki/Nominatim) for more information.

##### TomTom

The default language-locale is `en`, you can choose between `de`, `es`, `fr`,
`it`, `nl`, `pl`, `pt` and `sv`.

##### Yandex

The default language-locale is `ru-RU`, you can choose between `uk-UA`, `be-BY`,
`en-US`, `en-BR` and `tr-TR`. This provider can also reverse information based
on coordinates (latitude, longitude). It's possible to precise the toponym to
get more accurate result for reverse geocoding: `house`, `street`, `metro`,
`district` and `locality`.

#### IP-based Providers

Provider  | Name | IPv4? | IPv6? | Multiple? | Terms | Notes
:-------- |:---- |:----- |:----- |:--------- |:----- |:-----
[FreeGeoIp](http://freegeoip.net/) | `free_geo_ip` | yes | yes | no
[GeoIPs](http://www.geoips.com/en/) | `geo_ips` | yes | no | no | requires API key
[GeoIP2](https://www.maxmind.com/en/geoip2-databases) (Maxmind) | `maxmind_geoip2` | yes | yes | no
[GeoPlugin](http://www.geoplugin.com/) | `geo_plugin` | yes | yes | no
[HostIp](http://www.hostip.info/use.html) | `host_ip` | yes | no | no
[IpInfoDB](http://ipinfodb.com/) | `ip_info_db` | yes | no | no | requires API key | city precision
Geoip | `geoip` | yes | no | no | | wrapper around the [PHP extension](http://php.net/manual/en/book.geoip.php) which must be installed
[MaxMind](https://www.maxmind.com/) web service | `maxmind` | yes | yes | no | requires Omni API key | City/ISP/Org and Omni services, IPv6 on country level
MaxMind Binary file | `maxmind_binary` | yes | no | no | needs locally installed database files

**Important:** the [Geocoder
Extra](https://github.com/geocoder-php/geocoder-extra) library contains even
more official providers!

#### Locale Aware Providers

Providers that are _locale aware_ expose the following methods:

```php
$geocoder->setLocale('xyz');

$locale = $geocoder->getLocale();
```

#### The Chain Provider

The `Chain` provider is a special provider that takes a list of providers and
iterates over this list to get information. Note that it **stops** its iteration
when a provider returns a result. The result is returned by `GoogleMaps` because
`FreeGeoIp` and `HostIp` cannot geocode street addresses. `BingMaps` is ignored.

``` php
$geocoder = new \Geocoder\ProviderAggregator();
$adapter  = new \Ivory\HttpAdapter\CurlHttpAdapter();

$chain = new \Geocoder\Provider\Chain([
    new \Geocoder\Provider\FreeGeoIp($adapter),
    new \Geocoder\Provider\HostIp($adapter),
    new \Geocoder\Provider\GoogleMaps($adapter, 'fr_FR', 'France', true),
    new \Geocoder\Provider\BingMaps($adapter, '<API_KEY>'),
    // ...
]);

$geocoder->registerProvider($chain);

try {
    $geocode = $geocoder->geocode('10 rue Gambetta, Paris, France');
    var_export($geocode);
} catch (Exception $e) {
    echo $e->getMessage();
}
```

Everything is ok, enjoy!

### Dumpers

**Geocoder** provides dumpers that aim to transform an `Address` object in
standard formats.

#### GPS eXchange Format (GPX)

The **GPS eXchange** format is designed to share geolocated data like point of
interests, tracks, ways, but also coordinates. **Geocoder** provides a dumper to
convert an `Address` object in an GPX compliant format.

Assuming we got a `$address` object as seen previously:

``` php
<?php

$dumper = new \Geocoder\Dumper\Gpx();
$strGpx = $dumper->dump($address);

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

#### GeoJSON

[GeoJSON](http://geojson.org/) is a format for encoding a variety of geographic
data structures.

#### Keyhole Markup Language (KML)

[Keyhole Markup Language](http://en.wikipedia.org/wiki/Keyhole_Markup_Language)
is an XML notation for expressing geographic annotation and visualization within
Internet-based, two-dimensional maps and three-dimensional Earth browsers.


#### Well-Known Binary (WKB)

The Well-Known Binary (WKB) representation for geometric values is defined by
the OpenGIS specification.


#### Well-Known Text (WKT)

Well-known text (WKT) is a text markup language for representing vector geometry
objects on a map, spatial reference systems of spatial objects and
transformations between spatial reference systems.

### Formatters

A common use case is to print geocoded data. Thanks to the `StringFormatter`
class, it's simple to format an `Address` object as a string:

``` php
<?php

// $address is an instance of Address
$formatter = new \Geocoder\Formatter\StringFormatter();

$formatter->format($address, '%S %n, %z %L');
// 'Badenerstrasse 120, 8001 Zuerich'

$formatter->format($address, '<p>%S %n, %z %L</p>');
// '<p>Badenerstrasse 120, 8001 Zuerich</p>'
```

Here is the mapping:

* Street Number: `%n`

* Street Name: `%S`

* City: `%L`

* City District: `%D`

* Zipcode: `%z`

* Admin Level Name: `%A1`, `%A2`, `%A3`, `%A4`, `%A5`

* Admin Level Code: `%a1`, `%a2`, `%a3`, `%a4`, `%a5`

* Country: `%C`

* Country Code: `%c`

* Timezone: `%T`


Extending Things
----------------

You can write your own `provider` by implementing the `Provider` interface.

You can provide your own `dumper` by implementing the `Dumper` interface.


Versioning
----------

Geocoder follows [Semantic Versioning](http://semver.org/).

### End Of Life

#### 1.x

As of December 2014, branch `1.7` is not officially supported anymore, meaning
major version `1` reached end of life. Last version is:
[1.7.1](https://github.com/geocoder-php/Geocoder/releases/tag/1.7.1).

#### 2.x

As of December 2014, version [2.x](https://github.com/geocoder-php/Geocoder/tree/2.x)
is in a **feature frozen** state. All new features should be contributed to version 3.0
and upper. Last version is:
[2.8.1](https://github.com/geocoder-php/Geocoder/releases/tag/2.8.1).

Major version `2` will reach **end of life on December 2015**.

### Stable Version

Version `3.x` is the current major stable version of Geocoder.


Contributing
------------

See
[`CONTRIBUTING`](https://github.com/geocoder-php/Geocoder/blob/master/CONTRIBUTING.md#contributing)
file.


Unit Tests
----------

In order to run the test suite, install the development dependencies:

```
$ composer install --dev
```

Then, run the following command:

```
$ phpunit
```

You'll obtain some _skipped_ unit tests due to the need of API keys.

Rename the `phpunit.xml.dist` file to `phpunit.xml`, then uncomment the
following lines and add your own API keys:

``` xml
<php>
    <!-- <server name="IPINFODB_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="BINGMAPS_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="GEOIPS_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="MAXMIND_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="GEONAMES_USERNAME" value="YOUR_USERNAME" /> -->
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


Contributor Code of Conduct
---------------------------

As contributors and maintainers of this project, we pledge to respect all people
who contribute through reporting issues, posting feature requests, updating
documentation, submitting pull requests or patches, and other activities.

We are committed to making participation in this project a harassment-free
experience for everyone, regardless of level of experience, gender, gender
identity and expression, sexual orientation, disability, personal appearance,
body size, race, age, or religion.

Examples of unacceptable behavior by participants include the use of sexual
language or imagery, derogatory comments or personal attacks, trolling, public
or private harassment, insults, or other unprofessional conduct.

Project maintainers have the right and responsibility to remove, edit, or reject
comments, commits, code, wiki edits, issues, and other contributions that are
not aligned to this Code of Conduct. Project maintainers who do not follow the
Code of Conduct may be removed from the project team.

Instances of abusive, harassing, or otherwise unacceptable behavior may be
reported by opening an issue or contacting one or more of the project
maintainers.

This Code of Conduct is adapted from the [Contributor
Covenant](http:contributor-covenant.org), version 1.0.0, available at
[http://contributor-covenant.org/version/1/0/0/](http://contributor-covenant.org/version/1/0/0/)


License
-------

Geocoder is released under the MIT License. See the bundled LICENSE file for
details.
