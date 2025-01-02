# photon Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/photon-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/photon-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/photon-provider/v/stable)](https://packagist.org/packages/geocoder-php/photon-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/photon-provider/downloads)](https://packagist.org/packages/geocoder-php/photon-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/photon-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/photon-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/photon-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/photon-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/photon-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/photon-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the photon provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

## Install
```bash
composer require geocoder-php/photon-provider
```

## API Documentation
https://photon.komoot.io
https://github.com/komoot/photon

## Usage

### Basic usage
You can use your own photon instance :
```php
// New instance of the provider :
$provider = new Geocoder\Provider\Photon\Photon($httpClient, 'https://your-photon-root-url');
// Run geocode or reverse query
$query = $provider->geocodeQuery(\Geocoder\Query\GeocodeQuery::create('Paris'));
$reverseQuery = $provider->reverseQuery(\Geocoder\Query\ReverseQuery::fromCoordinates(48.86036 ,2.33852));
```

### OSM Tag Feature
You can search for location data based on osm tag filters.

For example, you can filter a geocode query to only include results of type 'place'. You can even restrict it to only have places of type 'city'.
In the reverse geocoding context you can search for the 3 pharmacies closest to a location.

To see what you can do with this feature, check [the official photon documentation](https://github.com/komoot/photon#filter-results-by-tags-and-values)

Below is an example to query the 3 pharmacies closest to a location :
```php
$provider = new Geocoder\Provider\Photon\Photon($httpClient, 'https://your-photon-root-url');
$reverseQuery = \Geocoder\Query\ReverseQuery::fromCoordinates(52.51644, 13.38890)
    ->withData('osm_tag', 'amenity:pharmacy')
    ->withLimit(3);

$results = $provider->reverseQuery($reverseQuery);
```

You can combine multiple osm tag filters :
```php
$provider = new Geocoder\Provider\Photon\Photon($httpClient, 'https://your-photon-root-url');
$reverseQuery = \Geocoder\Query\GeocodeQuery::create('Paris')
    ->withData('osm_tag', ['tourism:museum', 'tourism:gallery'])
    ->withLimit(5);
// Here we get 5 tourism results in Paris which are either museum or art gallery
$results = $provider->reverseQuery($reverseQuery);
```


## Contribute
Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
