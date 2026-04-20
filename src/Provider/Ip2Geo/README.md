# ip2geo Provider for Geocoder PHP

[![Latest Stable Version](https://poser.pugx.org/geocoder-php/ip2geo-provider/v/stable)](https://packagist.org/packages/geocoder-php/ip2geo-provider)
[![License](https://poser.pugx.org/geocoder-php/ip2geo-provider/license)](https://packagist.org/packages/geocoder-php/ip2geo-provider)

This is the [ip2geo](https://ip2geo.dev) provider for the [Geocoder PHP](https://github.com/geocoder-php/Geocoder) library.

## Installation

```bash
composer require geocoder-php/ip2geo-provider
```

## Usage

An API key is required. You can obtain one at [ip2geo.dev](https://ip2geo.dev).

```php
use Geocoder\Provider\Ip2Geo\Ip2Geo;
use Geocoder\Provider\Ip2Geo\Ip2GeoAddress;
use Geocoder\Query\GeocodeQuery;
use Http\Discovery\Psr18ClientDiscovery;

$httpClient = Psr18ClientDiscovery::find();
$provider = new Ip2Geo($httpClient, 'your-api-key');

$results = $provider->geocodeQuery(GeocodeQuery::create('8.8.8.8'));

/** @var Ip2GeoAddress $address */
$address = $results->first();
```

### Standard Geocoder Fields

These fields are available on all Geocoder Address objects:

```php
echo $address->getLocality();    // "Mountain View"
echo $address->getCountry();     // "United States"
echo $address->getCountryCode(); // "US"
echo $address->getTimezone();    // "America/Los_Angeles"
echo $address->getPostalCode();  // "94035"

$coordinates = $address->getCoordinates();
echo $coordinates->getLatitude();  // 37.386
echo $coordinates->getLongitude(); // -122.0838

$adminLevels = $address->getAdminLevels();
echo $adminLevels->get(1)->getName(); // "California"
echo $adminLevels->get(1)->getCode(); // "CA"
```

### Extra ip2geo Fields

The returned `Ip2GeoAddress` object extends the standard `Address` with additional
getters for all fields provided by the ip2geo API:

```php
// IP info
echo $address->getIp();     // "8.8.8.8"
echo $address->getIpType(); // "IPv4"
echo $address->isEu();      // false

// Continent
echo $address->getContinentName(); // "North America"
echo $address->getContinentCode(); // "NA"

// Country extras
echo $address->getPhoneCode(); // "+1"
echo $address->getCapital();   // "Washington D.C."
echo $address->getTld();       // ".us"

// Flag
echo $address->getFlagEmoji(); // (flag emoji)
echo $address->getFlagImg();   // "https://flagcdn.com/us.svg"

// Currency
echo $address->getCurrencyName();   // "United States Dollar"
echo $address->getCurrencyCode();   // "USD"
echo $address->getCurrencySymbol(); // "$"

// City extras
echo $address->getGeonameId();     // 5375480
echo $address->getAccuracyRadius(); // 1000
echo $address->getTimeNow();       // "2026-04-05T10:30:00-07:00"

// ASN
echo $address->getAsnNumber(); // 15169
echo $address->getAsnName();   // "Google LLC"

// Registered country
echo $address->getRegisteredCountryName(); // "United States"
echo $address->getRegisteredCountryCode(); // "US"
```

## Supported Operations

| Operation        | Supported |
|------------------|-----------|
| Geocode (IP)     | Yes       |
| Geocode (Street) | No        |
| Reverse          | No        |

## Running Tests

```bash
composer install
vendor/bin/phpunit
```

## License

This package is licensed under the [MIT License](LICENSE).
