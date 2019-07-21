# Geocode Earth Geocoder provider

[![Build Status](https://travis-ci.org/geocoder-php/geocode-earth-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/geocode-earth-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/geocode-earth-provider/v/stable)](https://packagist.org/packages/geocoder-php/geocode-earth-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/geocode-earth-provider/downloads)](https://packagist.org/packages/geocoder-php/geocode-earth-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/geocode-earth-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/geocode-earth-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/geocode-earth-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/geocode-earth-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/geocode-earth-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/geocode-earth-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Geocode Earth provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

### Install

```bash
composer require geocoder-php/geocode-earth-provider
```

### API Documentation

Geocode Earth uses the Pelias Geocoder under the hood. You can view it's [documentation here](https://github.com/pelias/documentation).  
The base API endpoint is <https://api.geocode.earth>.

### Pelias

Geocode Earth is based on [Pelias](https://github.com/pelias/pelias) geocoder!

If you want to use your own instance of Pelias (instead of Geocode Earth instance):

```php
$httpClient = new \Http\Adapter\Guzzle6\Client();
$provider = new \Geocoder\Provider\GeocodeEarth\GeocodeEarth(
    $httpClient,
    $apiKey,
    'http://localhost/', // Your Pelias instance URL
    1 // Your Pelias instance version
);
```

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
