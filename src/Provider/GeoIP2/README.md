# GeoIP2 Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/geoip2-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/geoip2-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/geoip2-provider/v/stable)](https://packagist.org/packages/geocoder-php/geoip2-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/geoip2-provider/downloads)](https://packagist.org/packages/geocoder-php/geoip2-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/geoip2-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/geoip2-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/geoip2-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/geoip2-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the GeoIP2 provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation. 

### Install

```bash
composer require geocoder-php/geoip2-provider
```

## Usage
The provider requires either a database file, or paid access to the web service.

### Using a database file
Both free [geolite2](https://dev.maxmind.com/geoip/geoip2/geolite2/) and the paid precision
 [city](https://www.maxmind.com/en/geoip2-city) and [country](https://www.maxmind.com/en/geoip2-country-database)
 databases are supported.

``` php
//Use a Maxmind GeoIP2 Database:
$reader = new \GeoIp2\Database\Reader('/path/to/geolite2.mmdb');

$adapter = new \Geocoder\Provider\GeoIP2\GeoIP2Adapter($reader);
$geocoder = new \Geocoder\Provider\GeoIP2\GeoIP2($adapter);

$address = $geocoder->geocode('74.200.247.59')->first();
```

### Using the Precision Web Service (API)
The provider also support the Precision Web Services. Please note that these API are paid, and billed per request.

``` php
// Use the Maxmind GeoIP2 API:
$reader = new \GeoIp2\WebService\Client(<account_id>, '<licence_key>');

$adapter = new \Geocoder\Provider\GeoIP2\GeoIP2Adapter($reader);
$geocoder = new \Geocoder\Provider\GeoIP2\GeoIP2($adapter);

$address = $geocoder->geocode('74.200.247.59')->first();
```

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
