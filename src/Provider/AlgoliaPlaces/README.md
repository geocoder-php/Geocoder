# Algolia Places Provider

[![Build Status](https://travis-ci.org/geocoder-php/algolia-places-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/algolia-places-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/algolia-places-provider/v/stable)](https://packagist.org/packages/geocoder-php/algolia-places-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/algolia-places-provider/downloads)](https://packagist.org/packages/geocoder-php/algolia-places-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/algolia-places-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/algolia-places-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/algolia-places-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/algolia-places-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/algolia-places-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/algolia-places-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Algolia Places provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

## Install

```bash
composer require geocoder-php/algolia-places-provider
```

## Usage

The Algolia Places API allows up to 1000 free requests per day (per IP) without authentication.

By [signing up for an account](https://www.algolia.com/users/sign_up/places) you can make 100,000 requests per month (~3000 a day).

See: https://community.algolia.com/places/pricing.html

### Locales

You should set a locale on the query. If it is missing, results may not be as complete for non english speaking countries.

```php

use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

$httpClient = new \GuzzleHttp\Client();

// Unauthenticated
$provider = new \Geocoder\Provider\AlgoliaPlaces\AlgoliaPlaces($httpClient);
// Authenticated
$provider = new \Geocoder\Provider\AlgoliaPlaces\AlgoliaPlaces($httpClient, '<your-key>', '<your-app-id>');

$geocoder = new \Geocoder\StatefulGeocoder($provider, 'en');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('Paris')->withLocale('fr-FR'));
```

## Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
