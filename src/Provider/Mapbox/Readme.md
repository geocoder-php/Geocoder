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
// optional: restrict the results to one or more ISO 3166 alpha-2 countries
$provider = new Mapbox($client, $accessToken, 'US');
// optional: store the results permanently (v6 `permanent` parameter)
$provider = new Mapbox($client, $accessToken, null, Mapbox::GEOCODING_MODE_PLACES_PERMANENT);

// Forward geocoding
$provider->geocodeQuery(GeocodeQuery::create('149 9th St, San Francisco, CA 94103'));

// Reverse geocoding
$provider->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911));
```

Options can be set as query data: `location_type` (one or more `Mapbox::TYPE_*`
constants), `autocomplete` (the v6 replacement for the v5 `fuzzy_match`) and the v6
Structured Input fields (`address_line1`, `address_number`, `street`, `block`, `place`,
`region`, `postcode`, `locality`, `neighborhood`), which replace the free-text query
when set.

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
