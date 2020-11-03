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

## Usage

```php
$httpClient = new \Http\Adapter\Guzzle6\Client();

$provider = new \Geocoder\Provider\ArcGISList\ArcGISList($httpClient);

$result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
```

### Storing results

ArcGIS prohibits storing the results of geocoding transactions without providing
a valid ArcGIS Online token, which requires
[ArcGIS Online credentials](https://developers.arcgis.com/rest/geocode/api-reference/geocoding-authenticate-a-request.htm).

You can use the static `token` method on the provider to create a client which
uses your valid ArcGIS Online token:

```php

$httpClient = new \Http\Adapter\Guzzle6\Client();

// Client ID is required. Private key is optional.
$provider = \Geocoder\Provider\ArcGISList\ArcGISList::token($httpClient, 'your-token');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
```

### Install

```bash
composer require geocoder-php/arcgis-online-provider
```

### Note

It is possible to specify a `sourceCountry` to restrict result to this specific
country thus reducing request time (note that this doesn't work on reverse
geocoding).


### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
