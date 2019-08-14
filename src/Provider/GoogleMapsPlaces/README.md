# Google Maps Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/google-maps-places-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/google-maps-places-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/google-maps-places-provider/v/stable)](https://packagist.org/packages/geocoder-php/google-maps-places-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/google-maps-places-provider/downloads)](https://packagist.org/packages/geocoder-php/google-maps-places-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/google-maps-places-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/google-maps-places-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/google-maps-places-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/google-maps-places-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/google-maps-places-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/google-maps-places-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Google Maps Places provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation. 

## Install

```bash
composer require geocoder-php/google-maps-places-provider
```

## API Documentation
https://developers.google.com/places/web-service

## Usage
This provider often requires extra data when making queries, due to requirements of the underlying places API.

### Geocoding
This provider supports two different modes of geocoding by text.

#### Find Mode
This is the default mode. It required an exact places name. It's not very forgiving, and generally only returns a single result

#### Search Mode
This mode will perform a search based on the input text. 
It's a lot more forgiving that the `find` mode, but results will contain all fields and thus be billed at the highest rate.

```php
$findResults = $provider->geocodeQuery(GeocodeQuery::create('Museum of Contemporary Art Australia')); // One Result

$searchResults = $provider->geocodeQuery(GeocodeQuery::create('art museum sydney'))
                    ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_SEARCH); // 20 Results
```

### Reverse Geocoding
When reverse geocoding, you are required to supply either a `keyword`, `type` or `name`.
See https://developers.google.com/places/web-service/search#PlaceSearchRequests

```php
$results = $provider->reverseQuery(ReverseQuery::fromCoordinates(-33.892674, 151.200727)->withData('type', 'bar'));
```

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
