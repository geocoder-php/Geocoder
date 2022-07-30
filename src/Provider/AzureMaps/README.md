# Azure Maps Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/azure-maps-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/azure-maps-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/azure-maps-provider/v/stable)](https://packagist.org/packages/geocoder-php/azure-maps-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/azure-maps-provider/downloads)](https://packagist.org/packages/geocoder-php/azure-maps-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/azure-maps-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/azure-maps-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/azure-maps-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/azure-maps-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/azure-maps-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/azure-maps-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Bing Maps provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

## Install

```bash
composer require geocoder-php/azure-maps-provider
```

## Usage

```php
$httpClient = new \GuzzleHttp\Client();

// You must provide a subscription key
$provider = new \Geocoder\Provider\AzureMaps\AzureMaps($httpClient, 'your-subscription-key');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('Yehuda Hamaccabi 15, Tel aviv'));
```

## Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).


