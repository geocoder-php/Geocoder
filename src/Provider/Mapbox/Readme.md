# Mapbox Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/mapbox-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/mapbox-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/mapbox-provider/v/stable)](https://packagist.org/packages/geocoder-php/mapbox-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/mapbox-provider/downloads)](https://packagist.org/packages/geocoder-php/mapbox-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/mapbox-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/mapbox-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/mapbox-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/mapbox-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/mapbox-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/mapbox-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Mapbox provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation. 

### Install

```bash
composer require geocoder-php/mapbox-provider
```

### Note

A valid `Access Token` is required for Mapbox.

### Usage

This provider targets the **Mapbox Geocoding API v6**. If you rely on the v5 API, pin
`geocoder-php/mapbox-provider` to `^1.5`.

```php
$provider = new Mapbox($client, $accessToken);
// optional: country filter (ISO 3166 alpha-2) and permanent result storage
$provider = new Mapbox($client, $accessToken, 'US', true);

// Forward geocoding
$provider->geocodeQuery(GeocodeQuery::create('149 9th St, San Francisco, CA 94103'));

// Forward geocoding with options (query data)
$query = GeocodeQuery::create('washi')
    ->withData('autocomplete', true)                    // prefix-style matching (on by default)
    ->withData('location_type', Mapbox::TYPE_STREET)    // or an array of Mapbox::TYPE_* constants
    ->withData('proximity', '-120.09,47.60')            // or 'ip'
    ->withData('worldview', 'us')
    ->withData('country', 'US');

// v6 Structured Input: typed fields replace the free-text query (the text is not sent)
$query = GeocodeQuery::create('2595 Lucky John Dr, Park City, UT 84060')
    ->withData('address_number', '2595')
    ->withData('street', 'Lucky John Dr')
    ->withData('place', 'Park City')
    ->withData('region', 'UT')
    ->withData('postcode', '84060');
// Any of address_line1, address_number, street, block, place, region, postcode,
// locality, neighborhood triggers Structured Input; autocomplete defaults to false.

// Reverse geocoding
$provider->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911));
```

Results are `Geocoder\Provider\Mapbox\Model\MapboxAddress` instances. Besides the regular
address data they expose `getId()`, `getStreetName()`, `getStreetNumber()`,
`getResultType()`, `getFormattedAddress()`, `getNeighborhood()`,
`getMatchCode()` / `getMatchConfidence()` (v6 Smart Address Match) and `getAccuracy()`.

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
