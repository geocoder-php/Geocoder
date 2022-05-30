# Google Places Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/google-maps-places-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/google-maps-places-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/google-maps-places-provider/v/stable)](https://packagist.org/packages/geocoder-php/google-maps-places-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/google-maps-places-provider/downloads)](https://packagist.org/packages/geocoder-php/google-maps-places-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/google-maps-places-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/google-maps-places-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/google-maps-places-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/google-maps-places-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/google-maps-places-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/google-maps-places-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Google Places provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

## Install
```bash
composer require geocoder-php/google-maps-places-provider
```

## API Documentation
https://developers.google.com/places/web-service

## Usage
This provider often requires extra data when making queries, due to requirements of the underlying Places API.

### Geocoding
This provider supports two different modes of geocoding by text.

#### Find Mode
This is the default mode. It required an exact places name. It's not very forgiving, and generally only returns a single result

```php
$results = $provider->geocodeQuery(
    GeocodeQuery::create('Museum of Contemporary Art Australia')
);
```

#### Search Mode
This mode will perform a search based on the input text.
It's a lot more forgiving that the `find` mode, but results will contain all fields and thus be billed at the highest rate.

```php
$results = $provider->geocodeQuery(
    GeocodeQuery::create('art museum sydney')
        ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_SEARCH)
);
```

around location (which is similar to reverse geocoding, see below):

```php
$results = $provider->geocodeQuery(
    GeocodeQuery::create('bar')
        ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_SEARCH)
        ->withData('location', '-32.926642, 151.783026')
);
```

country matches a country name or a two letter ISO 3166-1 country code. If you only use the "region" parameter, you will not be guaranteed to have results on the region, as the documentation indicates [Region](https://developers.google.com/maps/documentation/javascript/geocoding#GeocodingRequests):

> The region parameter will only influence, not fully restrict, results from the geocoder.

```php
$results = $provider->geocodeQuery(
    GeocodeQuery::create('montpellier')
        ->withData('components', 'country:FR');
);
```

### Reverse Geocoding
Three options available for reverse geocoding of latlon coordinates:

- mode `search` + type (e.g.) `bar`: uses Google Place API [Text search](https://developers.google.com/places/web-service/search#TextSearchRequests), requires `type`
  - is similar to: Search around location (see previous section)
- mode `nearby` + rankby `distance`: uses Google Place API [Nearby search](https://developers.google.com/places/web-service/search#PlaceSearchRequests), requires `type/keyword/name`
- mode `nearby` + rankby `prominence`: uses Google Place API [Nearby search](https://developers.google.com/places/web-service/search#PlaceSearchRequests), requires `radius`

Default mode: `search` (because of backward compatibility). When using mode `nearby` default rankby: `prominence`.
Mode `search` + type and mode `nearby` + type/keyword/name are very similar.
Mode `search` gives formatted_address, mode `nearby` gives vicinity instead.  E.g.:

- `search`: has "formatted_address": "7 Cope St, Redfern NSW 2016"
- `nearby`: has "vicinity" instead: "7 Cope St, Redfern"

Examples

```php
$results = $provider->reverseQuery(
    ReverseQuery::fromCoordinates(-33.892674, 151.200727)
        // ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_SEARCH) // =default
        ->withData('type', 'bar') // requires type
    );
$address = $results->first()->getFormattedAddress();
```

```php
$results = $provider->reverseQuery(
    ReverseQuery::fromCoordinates(-33.892674, 151.200727)
        ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_NEARBY)
        //->withData('rankby','prominence'); // =default
        ->withData('radius', 500) // requires radius (meters)
    );
$vicinity = $results->first()->getVicinity();
```

```php
$results = $provider->reverseQuery(
    ReverseQuery::fromCoordinates(-33.892674, 151.200727)
        ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_NEARBY)
        ->withData('rankby','distance');
        ->withData('keyword', 'bar') // requires type/keyword/name
    );
```

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
