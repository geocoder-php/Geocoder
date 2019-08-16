# Free GeoIp provider
[![Build Status](https://travis-ci.org/geocoder-php/free-geoip-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/free-geoip-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/free-geoip-provider/v/stable)](https://packagist.org/packages/geocoder-php/free-geoip-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/free-geoip-provider/downloads)](https://packagist.org/packages/geocoder-php/free-geoip-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/free-geoip-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/free-geoip-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/free-geoip-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/free-geoip-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/free-geoip-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/free-geoip-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Free GeoIp provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation. 

## Freegeoip Shutdown
As per the [freegeoip.net](http://freegeoip.net/shutdown) website, the provider has been purchased by [IpStack](https://ipstack.com/).
As a result, this provider no longer works with the default configuration. It will still work if you use the 
[self hosted variant](https://github.com/apilayer/freegeoip/) and supply a host when constructing the provider.

## Usage
```php
$httpClient = new \Http\Adapter\Guzzle6\Client();

// This will no longer work
$provider = new Geocoder\Provider\FreeGeoIp\FreeGeoIp($httpClient);
// You must provide the endpoint of your instance 
$provider = new Geocoder\Provider\FreeGeoIp\FreeGeoIp($httpClient, 'http://my.internal.geocoder/json/%s');
```

## Alternatives
We offer an [IpStack provider](https://github.com/geocoder-php/ipstack-provider) which you can use if you wish to continue with the new service owner.

### Full IP Provider List
https://github.com/geocoder-php/Geocoder#ip

## Install

```bash
composer require geocoder-php/free-geoip-provider
```

## Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
