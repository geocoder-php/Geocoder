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

### Note

It requires either the [database file](http://dev.maxmind.com/geoip/geoip2/geolite2/), or the
[webservice](http://dev.maxmind.com/geoip/geoip2/web-services/) - represented by
the GeoIP2 , which is injected to the `GeoIP2Adapter`. 

This provider will only work with the corresponding `GeoIP2Adapter`:

``` php
// Maxmind GeoIP2 Provider: e.g. the database reader
$reader = new \GeoIp2\Database\Reader('/path/to/database');

$adapter = new \Geocoder\Provider\GeoIP2\GeoIP2Adapter($reader);
$geocoder = new \Geocoder\Provider\GeoIP2\GeoIP2($adapter);

$address = $geocoder->geocode('74.200.247.59')->first();
```

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
