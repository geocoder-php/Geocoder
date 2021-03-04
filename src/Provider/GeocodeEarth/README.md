# Geocode Earth Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/geocode-earth-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/geocode-earth-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/geocode-earth-provider/v/stable)](https://packagist.org/packages/geocoder-php/geocode-earth-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/geocode-earth-provider/downloads)](https://packagist.org/packages/geocoder-php/geocode-earth-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/geocode-earth-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/geocode-earth-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/geocode-earth-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/geocode-earth-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/geocode-earth-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/geocode-earth-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Geocode Earth provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

## Usage

```php
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Geocoder\Provider\GeocodeEarth\GeocodeEarth;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

// Create a new HTTP adapter
$httpClient = new GuzzleAdapter();

// You must provide an API key
$provider = new GeocodeEarth($httpClient, 'your-api-key');

// Forward geocode
$forward = $provider->geocodeQuery(GeocodeQuery::create('11 Wall Street Manhattan')->withLimit(1));
print_locations($forward);

// Reverse geocode
$reverse = $provider->reverseQuery(ReverseQuery::fromCoordinates(40.707141, -74.010865)->withLimit(1));
print_locations($reverse);

// Print locations to console
function print_locations($results) {
  foreach($results as $key => $location) {
    printf(
      "%d) [%.6f, %0.6f] %s %s, %s, %s %s, %s\n",
      $key,
      $location->getCoordinates()->getLatitude(),
      $location->getCoordinates()->getLongitude(),
      $location->getStreetNumber(),
      $location->getStreetName(),
      $location->getSubLocality(),
      $location->getPostalCode(),
      $location->getLocality(),
      $location->getCountry()
    );
  }
}
```

```bash
0) [40.707141, -74.010865] 11 Wall Street, Financial District, 10005 New York, United States
0) [40.707141, -74.010865] 11 Wall Street, Financial District, 10005 New York, United States
```

All requests require a valid API key, however [free trials](https://geocode.earth/) are available.
Please see [the documentation](https://geocode.earth/docs) for information about authentication.

### Install

```bash
composer require geocoder-php/geocode-earth-provider
```

### API Documentation

You can view the complete [API documentation](https://geocode.earth/docs) on their website.
The base API endpoint is `https://api.geocode.earth`.

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
