# Google Maps Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/google-maps-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/google-maps-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/google-maps-provider/v/stable)](https://packagist.org/packages/geocoder-php/google-maps-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/google-maps-provider/downloads)](https://packagist.org/packages/geocoder-php/google-maps-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/google-maps-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/google-maps-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/google-maps-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/google-maps-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/google-maps-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/google-maps-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Google Maps provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

## Usage

```php
$httpClient = new \GuzzleHttp\Client();

// You must provide an API key
$provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient, null, 'your-api-key');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
```

All requests require a valid API key, however google does have a [free tier](https://cloud.google.com/maps-platform/pricing/) available.
Please see [this page for information on getting an API key](https://developers.google.com/maps/documentation/geocoding/get-api-key).

### Google Maps for Business

Previously, google offered a "Business" version of their APIs. The service has been deprecated, however existing clients
can use the static `business` method on the provider to create a client:

```php

$httpClient = new \GuzzleHttp\Client();

// Client ID is required. Private key is optional.
$provider = \Geocoder\Provider\GoogleMaps\GoogleMaps::business($httpClient, 'your-client-id', 'your-private-key');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
```

### Install

```bash
composer require geocoder-php/google-maps-provider
```



### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
