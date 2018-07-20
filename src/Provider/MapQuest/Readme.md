# MapQuest Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/mapquest-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/mapquest-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/mapquest-provider/v/stable)](https://packagist.org/packages/geocoder-php/mapquest-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/mapquest-provider/downloads)](https://packagist.org/packages/geocoder-php/mapquest-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/mapquest-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/mapquest-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/mapquest-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/mapquest-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/mapquest-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/mapquest-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the MapQuest provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation. 

### Install

```bash
composer require geocoder-php/mapquest-provider
```

### Geocode with more exact addresses

The MapQuest Provider allows you to create and pass geocode queries based on a full Address object of
class `Geocoder\Model\Address` or any other object that implements `Geocoder\Location`.

This will take advantage of what MapQuest calls the 5-box Input address format.
Quote from [MapQuest Developer: Specifying Locations](https://developer.mapquest.com/documentation/common/forming-locations/):
> The 5-Box Input address format (which is compatible with JSON and XML),
> allows for a higher degree of address specification by entering the full address in its individual location parameters.
> The 5-Box Input format is beneficial as it bypasses the parsing functionality of the single-line request.

If you have an object of a class that implements `Geocoder\Location` stored in the variable `$address`,
this new type of GeocodeQuery can be created with:
```
$query = GeocodeQuery::create('foobar');
$query = $query->withData(MapQuest::DATA_KEY_ADDRESS, $address);
```

If you want the GeocodeQuery to also work fine with all the other providers,
you will need to convert the `$address` object to a text string first.
Say you have stored this text string in the variable `$addressAsString`, the the example will read as follows:
```
$query = GeocodeQuery::create($addressAsString);
$query = $query->withData(MapQuest::DATA_KEY_ADDRESS, $address);
```

Here is a more complete example with use statements, and building of the address object:

**Example**
```
use Geocoder\Model\AddressBuilder;
use Geocoder\Provider\MapQuest\MapQuest;
use Geocoder\Query\GeocodeQuery;

$provider = new MapQuest($httpClient, $apiKey);

$addressBuilder = new AddressBuilder('Address provided by me');
$addressBuilder
  ->setStreetNumber(4868)
  ->setStreetName('Payne Rd');
  ->setLocality('Nashville');
  ->setSubLocality('Antioch');
  ->setAdminLevels([
      new AdminLevel(1, 'Tennessee', 'TN')
  ])
  ->setPostalCode('37013');
  ->setCountry('USA');
  ->setCountryCode('US');
$address = $addressBuilder->build();

$query = GeocodeQuery::create('dummy data');
$query = $query->withData(MapQuest::DATA_KEY_ADDRESS, $address);

$results = $provider->geocodeQuery($query);
```

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
