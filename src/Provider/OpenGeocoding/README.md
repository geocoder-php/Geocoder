# OpenGeocoding provider for Geocoder PHP

[![CI](https://github.com/geocoder-php/opengeocoding-provider/actions/workflows/provider.yml/badge.svg)](https://github.com/geocoder-php/opengeocoding-provider/actions/workflows/provider.yml)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/opengeocoding-provider/v/stable)](https://packagist.org/packages/geocoder-php/opengeocoding-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/opengeocoding-provider/downloads)](https://packagist.org/packages/geocoder-php/opengeocoding-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

A [Geocoder PHP](https://github.com/geocoder-php/Geocoder) provider for
[OpenGeocoding](https://opengeocoding.com), a hosted geocoding API built on open data
(OpenStreetMap, Overture Maps, OpenAddresses, GeoNames). The engine is
[source-available](https://github.com/Mokto/opengeocoding).

This is a **READ ONLY** repository. Contributions and issues belong in the
[main Geocoder repository](https://github.com/geocoder-php/Geocoder).

## Install

You need a [PSR-18 HTTP client](https://packagist.org/providers/psr/http-client-implementation), for example Guzzle:

```bash
composer require geocoder-php/opengeocoding-provider guzzlehttp/guzzle
```

If migrating from `opengeocoding/opengeocoding-provider`, remove that package first.
The PHP namespaces and API are unchanged; do not install both packages together.

Create an API key in the [OpenGeocoding dashboard](https://opengeocoding.com/dashboard). The
provider sends it in the `x-api-key` header, never in the URL.

## Usage

```php
use Geocoder\Provider\OpenGeocoding\OpenGeocoding;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Http\Discovery\Psr18Client;

$provider = new OpenGeocoding(new Psr18Client(), getenv('OPENGEOCODING_API_KEY'));

$address = $provider->geocodeQuery(GeocodeQuery::create('10 Rue de Rivoli, Paris'))->first();
echo $address->getCoordinates()->getLatitude();  // 48.8555
echo $address->getPostalCode();                   // 75004
echo $address->getDisplayName();                  // 10, Rue de Rivoli, Paris, Ile-de-France, France

$results = $provider->reverseQuery(ReverseQuery::fromCoordinates(55.676, 12.568)->withLimit(3));
foreach ($results as $address) {
    printf("%s (%.0f m)\n", $address->getDisplayName(), $address->getDistance());
}
```

The provider works with the rest of Geocoder PHP, such as `StatefulGeocoder`, `ProviderAggregator`,
the `Chain` provider and the cache plugin.

### Behaviour

- **Search returns at most one result**, the best match, whatever the query's limit. An address
  that matches nothing returns an empty collection.
- **Reverse** returns the nearest results first, up to the query's limit (at most 10). Pass a search
  radius in metres with `->withData('radiusM', 200)` (the API accepts up to 2000 and defaults to 500).
- **The locale is ignored**: the API has no language parameter.
- IP addresses are not supported (`UnsupportedOperation`).
- Admin levels come from the address's regions, ordered from the largest; their codes are the
  ISO 3166-2 subdivision codes (`IDF` for Île-de-France). Country codes are upper case.
- Results are `OpenGeocodingAddress` objects, with `getDisplayName()`, `isGoodMatch()` (false for a
  partial match, such as the street when the house number was not found) and, for reverse
  results, `getDistance()` in metres and `getLayer()` (`address`, `street` or `locality`).

### Errors

| HTTP status | Exception |
|---|---|
| 401, 403 | `Geocoder\Exception\InvalidCredentials` |
| 429 (rate limit or no credits left) | `Geocoder\Exception\QuotaExceeded` |
| 400 | `Geocoder\Exception\InvalidArgument`, with the API's message |
| anything else | `Geocoder\Exception\InvalidServerResponse` |

## Pricing

Plans and request limits are on the [pricing page](https://opengeocoding.com/pricing).

## Data attribution

Results come from open data under the ODbL and other open-data licences. When you show results,
or data derived from them, to others, credit the sources, for example:

> Data © OpenStreetMap contributors (ODbL 1.0), Overture Maps Foundation, OpenAddresses contributors, GeoNames (CC BY 4.0, modified). Details: https://opengeocoding.com/legal/attributions

Some countries' sources need their own credit line; see
[the data attributions page](https://opengeocoding.com/legal/attributions).

## Development

From `src/Provider/OpenGeocoding` in the main Geocoder repository:

```bash
composer update
composer test
```

The suite includes unit tests and the shared Geocoder integration tests, using cached
responses. Run `composer analyse` and `composer cs` from the main repository's root.

The tests replay the responses in `Tests/.cached_responses`. To record a new one, delete the
matching file and run the tests with `OPENGEOCODING_API_KEY` set. The key is sent in a header,
so it never reaches the cached files. Check that a recorded file holds a real result, not an
error, before you commit it.

## License

MIT. See [LICENSE](LICENSE).
