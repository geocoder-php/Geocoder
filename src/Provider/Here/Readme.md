# Here Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/here-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/here-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/here-provider/v/stable)](https://packagist.org/packages/geocoder-php/here-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/here-provider/downloads)](https://packagist.org/packages/geocoder-php/here-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/here-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/here-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/here-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/here-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/here-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/here-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Here provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

You can find the [documentation for the provider here](https://developer.here.com/documentation/geocoder/dev_guide/topics/resources.html).


### Install

```bash
composer require geocoder-php/here-provider
```

## Using

New applications on the Here platform use the `api_key` authentication method.

```php
$httpClient = new \Http\Adapter\Guzzle6\Client();

// You must provide an API key
$provider = \Geocoder\Provider\Here\Here::createUsingApiKey($httpClient, 'your-api-key');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
```

If you're using the legacy `app_code` authentication method, use the constructor on the provider like so:

```php
$httpClient = new \Http\Adapter\Guzzle6\Client();

// You must provide both the app_id and app_code
$provider = new \Geocoder\Provider\Here\Here($httpClient, 'app-id', 'app-code');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));
```

### Language parameter

Define the preferred language of address elements in the result. Without a preferred language, the Here geocoder will return results in an official country language or in a regional primary language so that local people will understand. Language code must be provided according to RFC 4647 standard.

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
