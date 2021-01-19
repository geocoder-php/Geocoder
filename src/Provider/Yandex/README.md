# Yandex Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/yandex-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/yandex-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/yandex-provider/v/stable)](https://packagist.org/packages/geocoder-php/yandex-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/yandex-provider/downloads)](https://packagist.org/packages/geocoder-php/yandex-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/yandex-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/yandex-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/yandex-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/yandex-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/yandex-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/yandex-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Yandex provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

### Install

```bash
composer require geocoder-php/yandex-provider
```

## Usage

The API now requires an API key. [See here for more information](https://yandex.ru/blog/mapsapi/novye-pravila-dostupa-k-api-kart?from=tech_pp).

```php
$httpClient = new \GuzzleHttp\Client();
$provider = new \Geocoder\Provider\Yandex\Yandex($httpClient, null, '<your-api-key>);

$result = $geocoder->geocodeQuery(GeocodeQuery::create('ул.Ленина, 19, Минск 220030, Республика Беларусь'));
$result = $geocoder->reverseQuery(ReverseQuery::fromCoordinates(...));
```

### Note

The default language-locale is `ru-RU`, you can choose between `uk-UA`, `be-BY`,
`en-US`, `en-BR` and `tr-TR`.

It's possible to precise the toponym to get more accurate result for reverse geocoding:
`house`, `street`, `metro`, `district` and `locality`.

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
