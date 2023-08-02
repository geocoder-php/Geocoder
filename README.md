# Geocoder

[![Geocoder](https://github.com/geocoder-php/Geocoder/actions/workflows/php.yml/badge.svg)](https://github.com/geocoder-php/Geocoder/actions/workflows/php.yml)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

> **Important:** You are browsing the documentation of Geocoder **4.x**.
>
> Documentation for version **3.x** is available here: [Geocoder 3.x
> documentation](https://github.com/geocoder-php/Geocoder/blob/3.x/README.md).
>
> Documentation for version **2.x** is available here: [Geocoder 2.x
> documentation](https://github.com/geocoder-php/Geocoder/blob/2.x/README.md).

---

**Geocoder** is a PHP library which helps you build geo-aware applications by
providing a powerful abstraction layer for geocoding manipulations.

* [Installation](#installation)
* [Cookbook](#cookbook)
* [Usage](#usage)
* [Providers](#providers)
* [Special Geocoders and Providers](#special-geocoders-and-providers)
* [Dumpers](#dumpers)
* [Formatters](#formatters)
* [Versioning](#versioning)

## Installation

To install a Geocoder there are two things you need to know:

1) What [Geocoder provider](https://packagist.org/providers/geocoder-php/provider-implementation) you want to use
2) What [HTTP client/adapter](https://packagist.org/providers/psr/http-client-implementation) you want to use.

### Geocoder providers

Since 4.0 we do not include providers by default. You need to select a *geocoder provider*. You will see a list of
providers [at Packagist](https://packagist.org/providers/geocoder-php/provider-implementation)

### HTTP Clients

In order to talk to geocoding APIs, you need HTTP adapters. While it was part of
the library in Geocoder before, Geocoder 4.x and upper now relies on [PSR-18](https://www.php-fig.org/psr/psr-18/)
which defines how HTTP message should be sent and received. You can use any library to send HTTP messages
that implements [psr/http-client-implementation](https://packagist.org/providers/psr/http-client-implementation).

Read more about PSR-18 in [this blog](https://www.php-fig.org/blog/2018/11/psr-18-the-php-standard-for-http-clients/).

### Summary (Just give me the command)

To install Google Maps geocoder with Guzzle 7 you may run the following command:

```cmd
composer require geocoder-php/google-maps-provider guzzlehttp/guzzle
```

Or using the curl client (you'll need to provide a PSR7 implementation such as `nyholm/psr7` if not using Guzzle)

```cmd
composer require geocoder-php/google-maps-provider php-http/curl-client nyholm/psr7
```

### Framework integration

If you are using a framework then you may be interested in our excellent framework integrations.

Framework      | Package   | Stats
:------------- |:--------- |:-------
[Laravel](https://github.com/geocoder-php/GeocoderLaravel) | `geocoder-php/GeocoderLaravel` | [![GitHub release](https://poser.pugx.org/toin0u/geocoder-laravel/v/stable)](https://packagist.org/packages/toin0u/geocoder-laravel) [![Packagist](https://poser.pugx.org/toin0u/geocoder-laravel/downloads)](https://packagist.org/packages/toin0u/geocoder-laravel)
[Symfony](https://github.com/geocoder-php/BazingaGeocoderBundle) | `geocoder-php/BazingaGeocoderBundle` | [![Latest Stable Version](https://poser.pugx.org/willdurand/geocoder-bundle/v/stable)](https://packagist.org/packages/willdurand/geocoder-bundle) [![Total Downloads](https://poser.pugx.org/willdurand/geocoder-bundle/downloads)](https://packagist.org/packages/willdurand/geocoder-bundle)

## Cookbook

We have a small cookbook where you can find examples on common use cases:

* [Caching responses](/docs/cookbook/cache.md)
* [Configuring the HTTP client](/docs/cookbook/http-client.md)
* [Rate limiting API requests](/docs/cookbook/rate-limiting.md)

## Usage

In the code snippet below we use GoogleMaps and Guzzle 7.

```php
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

$httpClient = new \GuzzleHttp\Client();
$provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient, null, 'your-api-key');
$geocoder = new \Geocoder\StatefulGeocoder($provider, 'en');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
$result = $geocoder->reverseQuery(ReverseQuery::fromCoordinates(...));
```

The `Provider` interface has three methods:

* `geocodeQuery(GeocodeQuery $query):AddressCollection`
* `reverseQuery(ReverseQuery $query):AddressCollection`
* `getName():string`

The `Geocoder` interface extends the `Provider` interface and exposes two additional methods. They will
make migration from 3.x smoother.

* `geocode($streetOrIpAddress)`
* `reverse($latitude, $longitude)`

## Providers

Providers perform the geocoding black magic for you (talking to the APIs, fetching results, dealing with errors, etc.)
and are highly configurable.

### Special providers

Provider       | Package | Features | Stats
:------------- |:------- |:-------- |:-------
[Cache](https://github.com/geocoder-php/cache-provider) | `geocoder-php/cache-provider` | Wraps a provider and cached the results | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/cache-provider/v/stable)](https://packagist.org/packages/geocoder-php/cache-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/cache-provider/downloads)](https://packagist.org/packages/geocoder-php/cache-provider)
[Chain](https://github.com/geocoder-php/chain-provider) | `geocoder-php/chain-provider` | Iterates over multiple providers | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/chain-provider/v/stable)](https://packagist.org/packages/geocoder-php/chain-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/chain-provider/downloads)](https://packagist.org/packages/geocoder-php/chain-provider)

### Address

#### World

Provider       | Package | Features | Stats
:------------- |:------- |:-------- |:-------
[Algolia Places](https://github.com/geocoder-php/algolia-places-provider) | `geocoder-php/algolia-places-provider` | address <br> [Website](https://community.algolia.com/places/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/algolia-places-provider/v/stable)](https://packagist.org/packages/geocoder-php/algolia-places-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/algolia-places-provider/downloads)](https://packagist.org/packages/geocoder-php/algolia-places-provider)
[ArcGIS Online](https://github.com/geocoder-php/arcgis-online-provider) | `geocoder-php/arcgis-online-provider` | address, reverse <br> [Website](https://developers.arcgis.com/en/features/geocoding/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/arcgis-online-provider/v/stable)](https://packagist.org/packages/geocoder-php/arcgis-online-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/arcgis-online-provider/downloads)](https://packagist.org/packages/geocoder-php/arcgis-online-provider)
[Azure Maps](https://github.com/geocoder-php/azure-maps-provider) | `geocoder-php/azure-maps-provider` | address, reverse <br> [Website](https://docs.microsoft.com/en-us/azure/azure-maps/tutorial-search-location) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/azure-maps-provider/v/stable)](https://packagist.org/packages/geocoder-php/azure-maps-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/azure-maps-provider/downloads)](https://packagist.org/packages/geocoder-php/azure-maps-provider)
[Bing Maps](https://github.com/geocoder-php/bing-maps-provider) | `geocoder-php/bing-maps-provider` | address, reverse <br> [Website](http://msdn.microsoft.com/en-us/library/ff701713.aspx) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/bing-maps-provider/v/stable)](https://packagist.org/packages/geocoder-php/bing-maps-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/bing-maps-provider/downloads)](https://packagist.org/packages/geocoder-php/bing-maps-provider)
[Geocode Earth](https://github.com/geocoder-php/geocode-earth-provider) | `geocoder-php/geocode-earth-provider` | address, reverse <br> [Website](https://geocode.earth/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/geocode-earth-provider/v/stable)](https://packagist.org/packages/geocoder-php/geocode-earth-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/geocode-earth-provider/downloads)](https://packagist.org/packages/geocoder-php/geocode-earth-provider)
[Geonames](https://github.com/geocoder-php/geonames-provider) | `geocoder-php/geonames-provider` | address, reverse <br> [Website](http://www.geonames.org/commercial-webservices.html) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/geonames-provider/v/stable)](https://packagist.org/packages/geocoder-php/geonames-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/geonames-provider/downloads)](https://packagist.org/packages/geocoder-php/geonames-provider)
[Google Maps](https://github.com/geocoder-php/google-maps-provider) <br> Google Maps for business | `geocoder-php/google-maps-provider` | address, reverse <br> [Website](https://developers.google.com/maps/documentation/geocoding/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/google-maps-provider/v/stable)](https://packagist.org/packages/geocoder-php/google-maps-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/google-maps-provider/downloads)](https://packagist.org/packages/geocoder-php/google-maps-provider)
[Google Maps Places](https://github.com/geocoder-php/google-maps-places-provider) | `geocoder-php/google-maps-places-provider` | address, reverse <br> [Website](https://developers.google.com/places/web-service/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/google-maps-places-provider/v/stable)](https://packagist.org/packages/geocoder-php/google-maps-places-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/google-maps-places-provider/downloads)](https://packagist.org/packages/geocoder-php/google-maps-places-provider)
[GraphHopper](https://github.com/geocoder-php/graphhopper-provider) | `geocoder-php/graphhopper-provider` | address, reverse <br> [Website](https://docs.graphhopper.com/#tag/Geocoding-API) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/graphhopper-provider/v/stable)](https://packagist.org/packages/geocoder-php/graphhopper-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/graphhopper-provider/downloads)](https://packagist.org/packages/geocoder-php/graphhopper-provider)
[Here](https://github.com/geocoder-php/here-provider) | `geocoder-php/here-provider` | address, reverse <br> [Website](https://developer.here.com/documentation/geocoder/topics/quick-start-geocode.html) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/here-provider/v/stable)](https://packagist.org/packages/geocoder-php/here-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/here-provider/downloads)](https://packagist.org/packages/geocoder-php/here-provider)
[LocationIQ](https://github.com/geocoder-php/locationiq-provider) | `geocoder-php/locationiq-provider` | address, reverse <br> [Website](https://locationiq.org/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/locationiq-provider/v/stable)](https://packagist.org/packages/geocoder-php/locationiq-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/locationiq-provider/downloads)](https://packagist.org/packages/geocoder-php/locationiq-provider)
[Mapbox](https://github.com/geocoder-php/mapbox-provider) | `geocoder-php/mapbox-provider` | address, reverse <br> [Website](https://www.mapbox.com/geocoding/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/mapbox-provider/v/stable)](https://packagist.org/packages/geocoder-php/mapbox-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/mapbox-provider/downloads)](https://packagist.org/packages/geocoder-php/mapbox-provider)
[MapQuest](https://github.com/geocoder-php/mapquest-provider) | `geocoder-php/mapquest-provider` | address, reverse <br> [Website](http://developer.mapquest.com/web/products/dev-services/geocoding-ws) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/mapquest-provider/v/stable)](https://packagist.org/packages/geocoder-php/mapquest-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/mapquest-provider/downloads)](https://packagist.org/packages/geocoder-php/mapquest-provider)
[MapTiler](https://github.com/geocoder-php/maptiler-provider) | `geocoder-php/maptiler-provider` | address, reverse <br> [Website](https://www.maptiler.com/cloud/geocoding/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/maptiler-provider/v/stable)](https://packagist.org/packages/geocoder-php/maptiler-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/maptiler-provider/downloads)](https://packagist.org/packages/geocoder-php/maptiler-provider)
[Nominatim](https://github.com/geocoder-php/nominatim-provider) <br> (OpenStreetMap) | `geocoder-php/nominatim-provider` | address, reverse <br> [Website](http://wiki.openstreetmap.org/wiki/Nominatim) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/nominatim-provider/v/stable)](https://packagist.org/packages/geocoder-php/nominatim-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/nominatim-provider/downloads)](https://packagist.org/packages/geocoder-php/nominatim-provider)
[OpenCage](https://github.com/geocoder-php/open-cage-provider) | `geocoder-php/open-cage-provider` | address, reverse <br> [Website](https://opencagedata.com/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/open-cage-provider/v/stable)](https://packagist.org/packages/geocoder-php/open-cage-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/open-cage-provider/downloads)](https://packagist.org/packages/geocoder-php/open-cage-provider)
[OpenRouteService](https://github.com/geocoder-php/openrouteservice-provider) | `geocoder-php/openrouteservice-provider` | address, reverse <br> [Website](https://openrouteservice.org/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/openrouteservice-provider/v/stable)](https://packagist.org/packages/geocoder-php/openrouteservice-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/openrouteservice-provider/downloads)](https://packagist.org/packages/geocoder-php/openrouteservice-provider)
[Pelias](https://github.com/geocoder-php/pelias-provider) | `geocoder-php/pelias-provider` | address, reverse <br> [Website](https://pelias.io/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/pelias-provider/v/stable)](https://packagist.org/packages/geocoder-php/pelias-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/pelias-provider/downloads)](https://packagist.org/packages/geocoder-php/pelias-provider)
[Photon](https://github.com/geocoder-php/photon-provider) | `geocoder-php/photon-provider` | address, reverse <br> [Website](https://photon.komoot.de/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/photon-provider/v/stable)](https://packagist.org/packages/geocoder-php/photon-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/photon-provider/downloads)](https://packagist.org/packages/geocoder-php/photon-provider)
[PickPoint](https://github.com/geocoder-php/pickpoint-provider) | `geocoder-php/pickpoint-provider` | address, reverse <br> [Website](https://pickpoint.io/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/pickpoint-provider/v/stable)](https://packagist.org/packages/geocoder-php/pickpoint-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/pickpoint-provider/downloads)](https://packagist.org/packages/geocoder-php/pickpoint-provider)
[TomTom](https://github.com/geocoder-php/tomtom-provider) | `geocoder-php/tomtom-provider` | address, reverse <br> [Website](https://geocoder.tomtom.com/app/view/index) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/tomtom-provider/v/stable)](https://packagist.org/packages/geocoder-php/tomtom-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/tomtom-provider/downloads)](https://packagist.org/packages/geocoder-php/tomtom-provider)
[Yandex](https://github.com/geocoder-php/yandex-provider) | `geocoder-php/yandex-provider` | address, reverse <br> [Website](http://api.yandex.com/maps/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/yandex-provider/v/stable)](https://packagist.org/packages/geocoder-php/yandex-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/yandex-provider/downloads)](https://packagist.org/packages/geocoder-php/yandex-provider)

#### Local

Region  | Provider      | Package | Features | Stats
:------ |:------------- |:------- |:-------- |:-------
:belgium: | [bpost](https://github.com/geo6/geocoder-php-bpost-provider) (third-party package*) | `geo6/geocoder-php-bpost-provider` | address <br> [Website](https://www.bpost.be/site/en/webservice-address) | [![Latest Stable Version](https://poser.pugx.org/geo6/geocoder-php-bpost-provider/v/stable)](https://packagist.org/packages/geo6/geocoder-php-bpost-provider) <br>[![Total Downloads](https://poser.pugx.org/geo6/geocoder-php-bpost-provider/downloads)](https://packagist.org/packages/geo6/geocoder-php-bpost-provider)
:belgium: | [GeoPunt](https://github.com/geo6/geocoder-php-geopunt-provider) (third-party package*) | `geo6/geocoder-php-geopunt-provider` | address, reverse <br> [Website](http://loc.geopunt.be/) | [![Latest Stable Version](https://poser.pugx.org/geo6/geocoder-php-geopunt-provider/v/stable)](https://packagist.org/packages/geo6/geocoder-php-geopunt-provider) <br>[![Total Downloads](https://poser.pugx.org/geo6/geocoder-php-geopunt-provider/downloads)](https://packagist.org/packages/geo6/geocoder-php-geopunt-provider)
:belgium: | [Service Public de Wallonie](https://github.com/geo6/geocoder-php-spw-provider) (third-party package*) | `geo6/geocoder-php-spw-provider` | address, reverse <br> [Website](http://geoservices.wallonie.be/geolocalisation/doc/ws/index.xhtml) | [![Latest Stable Version](https://poser.pugx.org/geo6/geocoder-php-spw-provider/v/stable)](https://packagist.org/packages/geo6/geocoder-php-spw-provider) <br>[![Total Downloads](https://poser.pugx.org/geo6/geocoder-php-spw-provider/downloads)](https://packagist.org/packages/geo6/geocoder-php-spw-provider)
:belgium: | [UrbIS](https://github.com/geo6/geocoder-php-urbis-provider) (third-party package*) | `geo6/geocoder-php-urbis-provider` | address, reverse <br> [Website](http://geoservices.irisnet.be/localization/) | [![Latest Stable Version](https://poser.pugx.org/geo6/geocoder-php-urbis-provider/v/stable)](https://packagist.org/packages/geo6/geocoder-php-urbis-provider) <br>[![Total Downloads](https://poser.pugx.org/geo6/geocoder-php-urbis-provider/downloads)](https://packagist.org/packages/geo6/geocoder-php-urbis-provider)
:fr: | [Addok](https://github.com/geo6/geocoder-php-addok-provider) (third-party package*) | `geo6/geocoder-php-addok-provider` | address, reverse <br> [Website](https://adresse.data.gouv.fr/) | [![Latest Stable Version](https://poser.pugx.org/geo6/geocoder-php-addok-provider/v/stable)](https://packagist.org/packages/geo6/geocoder-php-addok-provider) <br>[![Total Downloads](https://poser.pugx.org/geo6/geocoder-php-addok-provider/downloads)](https://packagist.org/packages/geo6/geocoder-php-addok-provider)
:luxembourg: | [Geoportail.lu](https://github.com/frantzmiccoli/geocoder-php-geoportail-lu) (third-party package*) | `frantzmicccoli/geocoder-php-geoportail-lu` | address, reverse <br> [Website](https://geoportail.lu/en/) | [![Latest Stable Version](https://poser.pugx.org/frantzmicccoli/geocoder-php-geoportail-lu/v/stable)](https://poser.pugx.org/frantzmicccoli/geocoder-php-geoportail-lu) <br>[![Total Downloads](https://poser.pugx.org/frantzmicccoli/geocoder-php-geoportail-lu/downloads)](https://packagist.org/packages/frantzmicccoli/geocoder-php-geoportail-lu)
:netherlands: | [Nationaal Georegister](https://github.com/swisnl/geocoder-php-nationaal-georegister-provider) (third-party package*) | `swisnl/geocoder-php-nationaal-georegister-provider` | address, reverse <br> [Website](https://geodata.nationaalgeoregister.nl/) | [![Latest Stable Version](https://poser.pugx.org/swisnl/geocoder-php-nationaal-georegister-provider/v/stable)](https://packagist.org/packages/swisnl/geocoder-php-nationaal-georegister-provider) <br>[![Total Downloads](https://poser.pugx.org/swisnl/geocoder-php-nationaal-georegister-provider/downloads)](https://packagist.org/packages/swisnl/geocoder-php-nationaal-georegister-provider)
:ru: | [Sputnik](https://github.com/demollc/sputnik-provider) (third-party package*) | `demollc/sputnik-provider` | address, reverse <br> [Website](http://api.sputnik.ru/maps/) | [![Latest Stable Version](https://poser.pugx.org/demollc/sputnik-provider/v/stable)](https://packagist.org/packages/demollc/sputnik-provider) <br>[![Total Downloads](https://poser.pugx.org/demollc/sputnik-provider/downloads)](https://packagist.org/packages/demollc/sputnik-provider)

\* *Third-party package: Those providers are not official, i.e. not from the Geocoder core team.*

### IP

Provider       | Package | Features | Stats
:------------- |:------- |:-------- |:-------
[FreeGeoIp](https://github.com/geocoder-php/free-geoip-provider) | `geocoder-php/free-geoip-provider` | IPv4, IPv6 <br> [Website](https://freegeoip.app/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/free-geoip-provider/v/stable)](https://packagist.org/packages/geocoder-php/free-geoip-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/free-geoip-provider/downloads)](https://packagist.org/packages/geocoder-php/free-geoip-provider)
[GeoIP2](https://github.com/geocoder-php/geoip2-provider) | `geocoder-php/geoip2-provider` | IPv4 <br> [Website](https://www.maxmind.com/en/geoip2-databases) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/geoip2-provider/v/stable)](https://packagist.org/packages/geocoder-php/geoip2-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/geoip2-provider/downloads)](https://packagist.org/packages/geocoder-php/geoip2-provider)
[GeoPlugin](https://github.com/geocoder-php/geo-plugin-provider) | `geocoder-php/geo-plugin-provider` | IPv4, IPv6 <br> [Website](http://www.geoplugin.com/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/geo-plugin-provider/v/stable)](https://packagist.org/packages/geocoder-php/geo-plugin-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/geo-plugin-provider/downloads)](https://packagist.org/packages/geocoder-php/geo-plugin-provider)
[HostIp](https://github.com/geocoder-php/host-ip-provider) | `geocoder-php/host-ip-provider` | IPv4 <br> [Website](http://www.hostip.info/use.html) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/host-ip-provider/v/stable)](https://packagist.org/packages/geocoder-php/host-ip-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/host-ip-provider/downloads)](https://packagist.org/packages/geocoder-php/host-ip-provider)
[IP2Location](https://github.com/geocoder-php/ip2location-provider) | `geocoder-php/ip2location-provider` | IPv4, IPv6 <br> [Website](https://www.ip2location.com/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/ip2location-provider/v/stable)](https://packagist.org/packages/geocoder-php/ip2location-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/ip2location-provider/downloads)](https://packagist.org/packages/geocoder-php/ip2location-provider)
[IP2Location Binary](https://github.com/geocoder-php/ip2location-binary-provider) | `geocoder-php/ip2location-binary-provider` | IPv4, IPv6 <br> [Website](https://www.ip2location.com/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/ip2location-binary-provider/v/stable)](https://packagist.org/packages/geocoder-php/ip2location-binary-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/ip2location-binary-provider/downloads)](https://packagist.org/packages/geocoder-php/ip2location-binary-provider)
[IpInfo](https://github.com/geocoder-php/ip-info-provider) | `geocoder-php/ip-info-provider` | IPv4, IPv6 <br> [Website](https://ipinfo.io/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/ip-info-provider/v/stable)](https://packagist.org/packages/geocoder-php/ip-info-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/ip-info-provider/downloads)](https://packagist.org/packages/geocoder-php/ip-info-provider)
[IpInfoDB](https://github.com/geocoder-php/ip-info-db-provider) | `geocoder-php/ip-info-db-provider` | IPv4 <br> [Website](http://ipinfodb.com/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/ip-info-db-provider/v/stable)](https://packagist.org/packages/geocoder-php/ip-info-db-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/ip-info-db-provider/downloads)](https://packagist.org/packages/geocoder-php/ip-info-db-provider)
[ipstack](https://github.com/geocoder-php/ipstack-provider) | `geocoder-php/ipstack-provider` | IPv4, IPv6 <br> [Website](https://ipstack.com/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/ipstack-provider/v/stable)](https://packagist.org/packages/geocoder-php/ipstack-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/ipstack-provider/downloads)](https://packagist.org/packages/geocoder-php/ipstack-provider)
[MaxMind](https://github.com/geocoder-php/maxmind-provider) | `geocoder-php/maxmind-provider` | IPv4, IPv6 <br> [Website](https://www.maxmind.com/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/maxmind-provider/v/stable)](https://packagist.org/packages/geocoder-php/maxmind-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/maxmind-provider/downloads)](https://packagist.org/packages/geocoder-php/maxmind-provider)
[MaxMind Binary](https://github.com/geocoder-php/maxmind-binary-provider) | `geocoder-php/maxmind-binary-provider` | IPv4, IPv6 <br> [Website](https://www.maxmind.com/) | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/maxmind-binary-provider/v/stable)](https://packagist.org/packages/geocoder-php/maxmind-binary-provider) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/maxmind-binary-provider/downloads)](https://packagist.org/packages/geocoder-php/maxmind-binary-provider)

### Other packages

There are two "*abstract*" or "*base*" packages that most providers depend on.

Name       | Package | Features | Stats
:--------- |:------- |:-------- |:-------
[PHP common](https://github.com/geocoder-php/php-common) | `willdurand/geocoder` | Models, interfaces, exceptions etc | [![Latest Stable Version](https://poser.pugx.org/willdurand/geocoder/v/stable)](https://packagist.org/packages/willdurand/geocoder) <br>[![Total Downloads](https://poser.pugx.org/willdurand/geocoder/downloads)](https://packagist.org/packages/willdurand/geocoder)
[HTTP common](https://github.com/geocoder-php/php-common-http) | `geocoder-php/common-http` | `AbstractHttpProvider`, HTTPlug | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/common-http/v/stable)](https://packagist.org/packages/geocoder-php/common-http) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/common-http/downloads)](https://packagist.org/packages/geocoder-php/common-http)
[Plugin](https://github.com/geocoder-php/plugin) | `geocoder-php/plugin` | Plugin provider | [![Latest Stable Version](https://poser.pugx.org/geocoder-php/plugin/v/stable)](https://packagist.org/packages/geocoder-php/plugin) <br>[![Total Downloads](https://poser.pugx.org/geocoder-php/plugin/downloads)](https://packagist.org/packages/geocoder-php/plugin)

## Special Geocoders and Providers

### The Chain Provider

The `Chain` provider is a special provider that takes a list of providers and
iterates over this list to get information. Note that it **stops** its iteration
when a provider returns a result. The result is returned by `GoogleMaps` because
`FreeGeoIp` and `HostIp` cannot geocode street addresses. `BingMaps` is ignored.

```php
use Geocoder\Query\GeocodeQuery;

$geocoder = new \Geocoder\ProviderAggregator();
$client  = new \GuzzleHttp\Client();

$chain = new \Geocoder\Provider\Chain\Chain([
    new \Geocoder\Provider\FreeGeoIp\FreeGeoIp($client),
    new \Geocoder\Provider\HostIp\HostIp($client),
    new \Geocoder\Provider\GoogleMaps\GoogleMaps($client, 'France'),
    new \Geocoder\Provider\BingMaps\BingMaps($client, '<API_KEY>'),
    // ...
]);

$geocoder->registerProvider($chain);

$result = $geocoder->geocodeQuery(GeocodeQuery::create('10 rue Gambetta, Paris, France'));
var_export($result);
```

Everything is ok, enjoy!

### The ProviderAggregator

The `ProviderAggregator` is used to register several providers so that you can
decide which provider to use later on.

```php
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

$client  = new \GuzzleHttp\Client();
$geocoder = new \Geocoder\ProviderAggregator();

$geocoder->registerProviders([
    new \Geocoder\Provider\GoogleMaps\GoogleMaps($client),
    new \Geocoder\Provider\GoogleMaps\GoogleMapsBusiness($client, '<CLIENT_ID>'),
    new \Geocoder\Provider\Yandex\Yandex($client),
    new \Geocoder\Provider\MaxMind\MaxMind($client, '<MAXMIND_API_KEY>'),
    new \Geocoder\Provider\ArcGISOnline\ArcGISOnline($client),
]);

$geocoder->registerProvider(new \Geocoder\Provider\Nominatim\Nominatim($adapter, 'https://your.nominatim.server'));

$geocoder
    ->using('google_maps')
    ->geocodeQuery(GeocodeQuery::create( ... ));

$geocoder
    ->limit(10)
    ->reverseQuery(ReverseQuery::fromCoordinates($lat, $lng));
```

The `ProviderAggregator`'s API is fluent, meaning you can write:

```php
use Geocoder\Query\GeocodeQuery;

$locations = $geocoder
    ->registerProvider(new \My\Provider\Custom($adapter))
    ->using('custom')
    ->limit(10)
    ->geocodeQuery(GeocodeQuery::create( ... ));
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
use Geocoder\Query\GeocodeQuery;

// configure your provider
$provider = // ...

$stopwatch = new \Symfony\Component\Stopwatch\Stopwatch();
$geocoder = new \Geocoder\TimedGeocoder($provider, $stopwatch);

$geocoder->geocodeQuery(GeocodeQuery::create('Paris, France'));

// Now you can debug your application
```

We use the [symfony/stopwatch](http://symfony.com/doc/current/components/stopwatch.html)
component under the hood. Which means, if you use the Symfony framework the
geocoder calls will appear in your timeline section in the Web Profiler.

### StatefulGeocoder

The `StatefulGeocoder` class is great when you want your Geocoder to hold state. Say you want to configure locale,
limit or bounds in runtime. The `StatefulGeocoder` will append these values on each query.

```php
use Geocoder\Query\GeocodeQuery;

// configure your provider
$provider = // ...
$geocoder = new \Geocoder\StatefulGeocoder($provider);

$geocoder->setLocale('en');
$results = $geocoder->geocodeQuery(GeocodeQuery::create('London'));
echo $results->first()->getLocality(); // London

$geocoder->setLocale('es');
$results = $geocoder->geocodeQuery(GeocodeQuery::create('London'));
echo $results->first()->getLocality(); // Londres
```

## Dumpers

**Geocoder** provides dumpers that aim to transform a `Location` object in
standard formats.

### GPS eXchange Format (GPX)

The **GPS eXchange** format is designed to share geolocated data like point of
interests, tracks, ways, but also coordinates. **Geocoder** provides a dumper to
convert a `Location` object in an GPX compliant format.

Assuming we got a `$location` object as seen previously:

```php
$dumper = new \Geocoder\Dumper\Gpx();
$strGpx = $dumper->dump($location);

echo $strGpx;
```

It will display:

```xml
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

### GeoJSON

[GeoJSON](http://geojson.org/) is a format for encoding a variety of geographic
data structures.

### GeoArray

Simple PHP array format for using with your own encoders.

### Keyhole Markup Language (KML)

[Keyhole Markup Language](http://en.wikipedia.org/wiki/Keyhole_Markup_Language)
is an XML notation for expressing geographic annotation and visualization within
Internet-based, two-dimensional maps and three-dimensional Earth browsers.

### Well-Known Binary (WKB)

The Well-Known Binary (WKB) representation for geometric values is defined by
the OpenGIS specification.

### Well-Known Text (WKT)

Well-known text (WKT) is a text markup language for representing vector geometry
objects on a map, spatial reference systems of spatial objects and
transformations between spatial reference systems.

## Formatters

A common use case is to print geocoded data. Thanks to the `StringFormatter`
class, it's simple to format a `Location` object as a string:

```php
// $location is an instance of Location
$formatter = new \Geocoder\Formatter\StringFormatter();

$formatter->format($location, '%S %n, %z %L');
// 'Badenerstrasse 120, 8001 Zuerich'

$formatter->format($location, '<p>%S %n, %z %L</p>');
// '<p>Badenerstrasse 120, 8001 Zuerich</p>'
```

Here is the mapping:

* Street Number: `%n`
* Street Name: `%S`
* City (Locality): `%L`
* City District (Sub-Locality): `%D`
* Zipcode (Postal Code): `%z`
* Admin Level Name: `%A1`, `%A2`, `%A3`, `%A4`, `%A5`
* Admin Level Code: `%a1`, `%a2`, `%a3`, `%a4`, `%a5`
* Country: `%C`
* Country Code: `%c`
* Timezone: `%T`

## Versioning

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

#### 3.x

As of January 2017, version [3.x](https://github.com/geocoder-php/Geocoder/tree/3.x)
is in a **feature frozen** state. All new features should be contributed to version 4.0
and upper. Last version is:
[3.3.2](https://github.com/geocoder-php/php-common/releases/tag/v3.3.2).

Major version `3` will reach **end of life on October 2017**.

### Stable Version

Version `4.x` is the current major stable version of Geocoder.

### Next version

There is no new major version planned at this time.

## Contributing

See [`CONTRIBUTING`](https://github.com/geocoder-php/Geocoder/blob/master/CONTRIBUTING.md#contributing) file.

## Unit Tests

In order to run the test suite, install the development dependencies:

```cmd
composer install --dev
```

Then, run the following command:

```cmd
composer test
```

You'll obtain some _skipped_ unit tests due to the need of API keys.

Rename the `phpunit.xml.dist` file to `phpunit.xml`, then uncomment the
following lines and add your own API keys:

```xml
<php>
    <!-- <server name="IPINFODB_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="BINGMAPS_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="GEOIPS_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="MAXMIND_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="GEONAMES_USERNAME" value="YOUR_USERNAME" /> -->
    <!-- <server name="TOMTOM_MAP_KEY" value="YOUR_MAP_KEY" /> -->
    <!-- <server name="GOOGLE_GEOCODING_KEY" value="YOUR_GEOCODING_KEY" /> -->
    <!-- <server name="OPENCAGE_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="PICKPOINT_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="LOCATIONIQ_API_KEY" value="YOUR_API_KEY" /> -->
    <!-- <server name="IPSTACK_API_KEY" value="YOUR_API_KEY" /> -->
</php>
```

You're done.

## Credits

* William Durand <will+git@drnd.me>
* Tobias Nyholm <tobias.nyholm@gmail.com>
* [All contributors](https://github.com/geocoder-php/Geocoder/contributors)

## License

Geocoder is released under the MIT License. See the bundled LICENSE file for details.
