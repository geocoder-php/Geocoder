# Storage Location Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/storage-location-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/storage-location-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/storage-location-provider/v/stable)](https://packagist.org/packages/geocoder-php/storage-location-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/storage-location-provider/downloads)](https://packagist.org/packages/geocoder-php/storage-location-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/storage-location-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/storage-location-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/storage-location-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/storage-location-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/storage-location-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/storage-location-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Storage Location provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

### Install

```bash
composer require geocoder-php/storage-location-provider
```

### Benefits

* save requests to real provider
* own-driven performance control
* opportunity to build own locations, places
* possible to use high precise value for coordinates (storing as float type)

### Usage

First of all you need to setup storage where you will save data about locations. Currently available all database providers what match PSR-6.

```php
$database = new \Symfony\Component\Cache\Adapter\FilesystemAdapter();
```

After, you need to setup database configuration. If you don't want do it, you can use default configuration by creating class without any arguments.

```php
$dbConfig = new \Geocoder\Provider\StorageLocation\Model\DBConfig();
```

After that you can use Storage Location provider:

```php
$provider = new \Geocoder\Provider\StorageLocation\StorageLocation($database, $dbConfig);
```

Please take attention what you need to take care for save data in database what you use. Also in first moment you need to add places what you want to find in feature. Each place in database it's specific Place entity what contain collection of Address entities and additional properties - `Polygons`, `currentLocale` and `objectHash`.

```php
$headers = [
    'Accept-language' => 'en'
];

/* query for Kiev city, Ukraine */
$query = [
    'format' => 'geocodejson',
    'osm_ids' => 'R421866',
    'polygon_geojson' => 1,
    'addressdetails' => 1,
];

$request = new \http\Client\Request('GET', 'https://nominatim.openstreetmap.org/lookup?' . http_build_query($query), $headers);
/** @var \Psr\Http\Message\ResponseInterface $response */
$response = new \Http\Client\HttpClient($request);
$rawGeoCodeJson = json_decode((string)$response->getBody());

$query['format'] = 'geojson';
unset($query['polygon_geojson'], $query['addressdetails']);

$request = new \http\Client\Request('GET', 'https://nominatim.openstreetmap.org/lookup?' . http_build_query($query), $headers);
$response = new \Http\Client\HttpClient($request);
$rawGeoJson = json_decode((string)$response->getBody());

$rawGeoCodeJson['features'][0]['bbox'] = $rawGeoJson['features'][0]['bbox'];
$rawGeoCodeJson['properties']['geocoding']['country_code'] = $rawGeoJson['features'][0]['properties']['address']['country_code'];

$provider->addPlace(mapRawDataToPlace($rawGeoCodeJson));

function mapRawDataToPlace(array $rawData): \Geocoder\Provider\StorageLocation\Model\Place
    {
        $root = $rawData['features'][0];

        $adminLevels = [];
        foreach ($root['properties']['geocoding']['admin'] as $adminLevel => $name) {
            $level = (int)substr($adminLevel, 5);
            if ($level > 5) {
                $level = 5;
            } elseif ($level < 1) {
                $level = 1;
            }

            $adminLevels[$level] = new \Geocoder\Model\AdminLevel($level, $name);
        }

        $polygons = [];
        foreach ($root['geometry']['coordinates'] as $rawPolygon) {
            $tempPolygon = new \Geocoder\Provider\StorageLocation\Model\Polygon();
            foreach ($rawPolygon as $coordinates) {
                $tempPolygon->addCoordinates(new \Geocoder\Model\Coordinates($coordinates[1], $coordinates[0]));
            }
            $polygons[] = $tempPolygon;
        }

        return new \Geocoder\Provider\StorageLocation\Model\Place(
            ['en' => new \Geocoder\Model\Address(
                $rawData['geocoding']['attribution'],
                new \Geocoder\Model\AdminLevelCollection($adminLevels),
                new \Geocoder\Model\Coordinates($root['coordinates'][1], $root['coordinates'][0]),
                new \Geocoder\Model\Bounds($root['bbox'][0], $root['bbox'][1], $root['bbox'][2], $root['bbox'][3]),
                $root['properties']['geocoding']['housenumber'] ?? '',
                $root['properties']['geocoding']['street'] ?? '',
                $root['properties']['geocoding']['postcode'] ?? '',
                $root['properties']['geocoding']['state'] ?? '',
                $root['properties']['geocoding']['city'] ?? '',
                new \Geocoder\Model\Country($root['properties']['geocoding']['country'], $root['properties']['geocoding']['country_code']),
                null
            )],
            $polygons
        );
    }
```

After add place above you will receive that place in `reverseQuery` for any coordinate what consisting in Place's polygons. If you will add place with highest admin level - you will receive that new place. That provider every time try to respond places with highest admin level (for `reverseQuery` method).

```php
$address = $provider->reverseQuery(new \Geocoder\Query\ReverseQuery(new \Geocoder\Model\Coordinates(50.4422519, 30.5423135)));
```

For `geocodeQuery` use it in usual way.

```php
$address = $provider->geocodeQuery(new \Geocoder\Query\GeocodeQuery('Kyiv, Ukraine'));
```

### Working with Database

That provider have additional methods for realize database functionality:
* `addPlace` - add Place object, return boolean
* `deletePlace` - delete Place object, return boolean
* `getAllPlaces` - get all existent places in db, return array of `\Geocoder\Provider\StorageLocation\Model\Place`

Take attention what each Place object identified in database according to `objectHash` property. Please use that property as read-only. If you will change that property, database provider will lose relation to that Place in database.

Take attention what each Address object identified in database according:
1. Admin level - admin level name
2. Country code, postal code, locality, subLocality, streetName, streetNumber

If you want to change Place entity you should delete that Place and add new Place with already changed object. Also please take attention what each object in database have time to life value. By default it's 365 days (1 year), you can setup it through passing specific argument in creation `\Geocoder\Provider\StorageLocation\Model\DBConfig`.

### Testing

Please run `composer test`.

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
