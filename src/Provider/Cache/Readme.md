# Cache provider
[![Build Status](https://travis-ci.org/geocoder-php/cache-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/cache-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/cache-provider/v/stable)](https://packagist.org/packages/geocoder-php/cache-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/cache-provider/downloads)](https://packagist.org/packages/geocoder-php/cache-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/cache-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/cache-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/cache-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/cache-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/cache-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/cache-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the a cache provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

### Install

```bash
composer require geocoder-php/cache-provider
```

### Usage
The `ProviderCache` allows you to use any [PSR-6](https://www.php-fig.org/psr/psr-6/) compatible cache driver.
You can find compatible drivers on [packagist](https://packagist.org/providers/psr/cache-implementation).

By default, the result is cached forever.
You can  set a cache expiry by passing an integer representing the number of seconds as the third parameter.

```php
$httpClient = new \GuzzleHttp\Client();
$provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient);

$psr6Cache = new ArrayCachePool(); // Requires `cache/array-adapter` package

$cachedProvider = new \Geocoder\Provider\Cache\ProviderCache(
    $provider, // Provider to cache
    $psr6Cache, // PSR-6 compatible cache
    600 // Cache expiry, in seconds
);

$geocoder = new \Geocoder\StatefulGeocoder($cachedProvider, 'en');

// Will come from Google Maps API
$result1 = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
// Will come from the cache
$result2 = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
```

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
