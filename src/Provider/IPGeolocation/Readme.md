# IPGeolocation.io Geocoder provider
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/ipgeolocation-provider/v/stable)](https://packagist.org/packages/geocoder-php/ipgeolocation-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/ipgeolocation-provider/downloads)](https://packagist.org/packages/geocoder-php/ipgeolocation-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/ipgeolocation-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/ipgeolocation-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the [IPGeolocation.io](https://ipgeolocation.io/) provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation.

### Install

```bash
composer require geocoder-php/ipgeolocation-provider
```

### Usage

The provider geocodes IPv4 and IPv6 addresses. It does not support street addresses or reverse geocoding.

```php
$httpClient = new \Http\Discovery\Psr18Client();
$provider = new \Geocoder\Provider\IPGeolocation\IPGeolocation($httpClient, 'your-api-key');

$result = $provider->geocodeQuery(\Geocoder\Query\GeocodeQuery::create('8.8.8.8'));
```

Sign up for a free API key at [app.ipgeolocation.io](https://app.ipgeolocation.io/login).

### Note

Results are returned in English by default. Pass a locale with `GeocodeQuery::withLocale()` to request another
language; languages other than English need a paid plan.

IP addresses that are private, reserved or missing from the IPGeolocation.io database return an empty collection.

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
