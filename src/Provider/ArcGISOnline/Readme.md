# ArcGIS Online

[![Build Status](https://travis-ci.org/geocoder-php/arcgis-online-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/arcgis-online-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/arcgis-online-provider/v/stable)](https://packagist.org/packages/geocoder-php/arcgis-online-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/arcgis-online-provider/downloads)](https://packagist.org/packages/geocoder-php/arcgis-online-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/arcgis-online-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/arcgis-online-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/arcgis-online-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/arcgis-online-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/arcgis-online-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/arcgis-online-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the ArcGIS provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

ArcGIS provides 2 APIs for geocoding addresses:
* [`geocodeAddresses`](https://developers.arcgis.com/rest/geocode/api-reference/geocoding-geocode-addresses.htm)
* [`findAddressCandidates`](https://developers.arcgis.com/rest/geocode/api-reference/geocoding-find-address-candidates.htm)
    * This API states:
    > Applications are contractually prohibited from storing the results of
    geocoding transactions unless they make the request by passing the
    `forStorage` parameter with a value of `true` and the `token` parameter with
    a valid ArcGIS Online token.

Since a token is required for the `geocodeAddresses` API, the
`geocodeQuery` method checks the `token` property:
* If `token` is `NULL`, it uses the `findAddressCandidates` API.
* If `token` is not `NULL`, it uses the `geocodeAddresses` API.
    * If the `token` value is invalid or has expired, you will get an error.
    * Tokens have a maximum lifetime of 14 days.
    * [Instructions for generating an ArcGIS token](https://developers.arcgis.com/rest/geocode/api-reference/geocoding-authenticate-a-request.htm#GUID-F2BECC7B-5042-4D89-87FC-4CE31012E66D)

## Usage

### Without a token

```php
$httpClient = new \GuzzleHttp\Client();

$provider = new \Geocoder\Provider\ArcGISList\ArcGISList($httpClient);

// Uses the `findAddressCandidates` operation. Result storage is prohibited.
$result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
```

### With a token

```php

$httpClient = new \GuzzleHttp\Client();

// Your token is required.
$provider = \Geocoder\Provider\ArcGISList\ArcGISList::token($httpClient, 'your-token');

// Uses the `geocodeAddresses` operation. Result storage is permitted.
$result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
```

### Install

```bash
composer require geocoder-php/arcgis-online-provider
```

### Note

It is possible to specify a `sourceCountry` to restrict results to this specific
country thus reducing request time (note that this doesn't work on reverse
geocoding).


### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
