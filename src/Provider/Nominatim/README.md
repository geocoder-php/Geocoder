# Nominatim Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/nominatim-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/nominatim-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/nominatim-provider/v/stable)](https://packagist.org/packages/geocoder-php/nominatim-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/nominatim-provider/downloads)](https://packagist.org/packages/geocoder-php/nominatim-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/nominatim-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/nominatim-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/nominatim-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/nominatim-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/nominatim-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/nominatim-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Nominatim provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

All usage of the Nominatim provider using `nominatim.openstreetmap.org` must follow the [Nominatim Usage Policy](https://operations.osmfoundation.org/policies/nominatim/) !

## Install

```bash
composer require geocoder-php/nominatim-provider
```

## Usage

If you want to use the "default" Nominatim instance (https://nominatim.openstreetmap.org/) :

```php
$provider = \Geocoder\Provider\Nominatim\Nominatim::withOpenStreetMapServer($httpClient, $userAgent);
```

If you want to specify yourself the server that will be used :

```php
$provider = new \Geocoder\Provider\Nominatim($httpClient, 'https://nominatim.openstreetmap.org', $userAgent);
```

## Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
