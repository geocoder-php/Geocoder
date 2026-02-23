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

## API Versions

This provider supports two HERE API generations:

| | Geocoding & Search API | Legacy Geocoder REST API |
|---|---|---|
| This provider calls it | **"v8"** (new default) | **"v7"** (deprecated) |
| HERE's own name | "Geocoding & Search API v7" / GS7 | "Geocoder REST API" / 6.2 |
| URL path | `/v1/` | `/6.2/` |
| Authentication | API Key only | API Key or App ID + App Code |
| Deprecated by HERE | — | **December 31, 2023** |
| Shut down by HERE | — | **July 2025** |
| Removed from this provider | — | **Next major release** |

> **Note on version naming:** HERE's legacy API uses URL path `/6.2/` and is called the "Geocoder REST
> API". HERE confusingly named its replacement the "Geocoding & Search API **v7**" (also known as GS7).
> To avoid this collision, this provider uses the shorthand **"v8"** for the new Geocoding & Search API
> and **"v7"** for the legacy 6.2 API.

**Timeline:**
- **December 31, 2023** — HERE deprecated the legacy Geocoder REST API (v7).
- **July 2025** — HERE shut down the v7 endpoints. Live requests to `geocoder.ls.hereapi.com` will fail.
- **Next major release of this provider** — The v7 compatibility code will be removed: `createV7UsingApiKey()`, `new Here($client, $appId, $appCode)`, all `GEOCODE_ENDPOINT_URL_*` / `REVERSE_ENDPOINT_URL_*` v7 constants, and `parseV7Response()`.

Migrate to v8 as soon as possible.
See the [HERE migration guide](https://www.here.com/docs/bundle/geocoding-and-search-api-migration-guide/page/migration-geocoder/README.html) for details.

### Install

```bash
composer require geocoder-php/here-provider
```

## Using v8 (Recommended)

New and existing applications should use `createUsingApiKey()`, which targets the v8 Geocoding & Search API:

```php
$httpClient = new \Http\Discovery\Psr18Client();

// Provide your HERE API Key
$provider = \Geocoder\Provider\Here\Here::createUsingApiKey($httpClient, 'your-api-key');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('10 Downing St, London, UK'));
```

### v8 Query Parameters

The v8 API supports the following extra parameters via `GeocodeQuery::withData()`:

| Parameter | Description |
|-----------|-------------|
| `at` | Reference position for result sorting, e.g. `"52.5,13.4"` |
| `in` | Geographic area filter, e.g. `"countryCode:DEU"` |
| `types` | Filter result types, e.g. `"houseNumber,street"` |
| `country` | ISO 3166-1 alpha-3 country code filter (mapped to `qq` param) |
| `state` | State/region filter (mapped to `qq` param) |
| `county` | County filter (mapped to `qq` param) |
| `city` | City filter (mapped to `qq` param) |

### v8 Response Fields

In addition to standard Geocoder fields, `HereAddress` provides:

- `getLocationId()` — unique HERE location ID
- `getLocationType()` — result type (`houseNumber`, `street`, `locality`, `administrativeArea`, etc.)
- `getLocationName()` — formatted title of the result
- `getAdditionalDataValue($name)` — access extra fields such as `Label`, `CountryName`, `StateName`, `CountyName`, `CountyCode`, `StateCode`, `District`, `Subdistrict`, `HouseNumberType`, etc.

## Using v7 (Deprecated — Shut down July 2025)

> **Warning:** The HERE Geocoder REST API v7 was deprecated December 31, 2023 and shut down in
> July 2025. Requests will fail. Migrate to v8 using `createUsingApiKey()` above.
> See the [HERE retirement announcement](https://www.here.com/learn/blog/additional-important-guidance-on-here-location-services-end-of-life) for details.

If you have existing code that uses the legacy API Key authentication:

```php
$httpClient = new \Http\Discovery\Psr18Client();

// @deprecated — Migrate to createUsingApiKey() for the v8 API
$provider = \Geocoder\Provider\Here\Here::createV7UsingApiKey($httpClient, 'your-legacy-api-key');
```

If you're using the legacy `app_id` + `app_code` authentication:

```php
$httpClient = new \Http\Discovery\Psr18Client();

// @deprecated — Migrate to createUsingApiKey() for the v8 API
$provider = new \Geocoder\Provider\Here\Here($httpClient, 'app-id', 'app-code');
```

## Migrating from v7 to v8

1. Replace `new Here($client, $appId, $appCode)` or `createV7UsingApiKey(...)` with `createUsingApiKey($client, $apiKey)`.
2. The response structure changes: `additionalData` values like `CountryName`, `StateName`, `CountyName` are still available via `getAdditionalDataValue()`, but are now sourced from v8 address fields.
3. The `shape` data (v7 `IncludeShapeLevel` parameter) is not available in v8. Remove `withData('IncludeShapeLevel', ...)` from your queries.
4. Replace v7-specific `withData()` keys (`Country2`, `IncludeRoutingInformation`, `IncludeChildPOIs`, etc.) with v8 equivalents where available.

See the [official migration guide](https://www.here.com/docs/bundle/geocoding-and-search-api-migration-guide/page/migration-geocoder/README.html) for a full parameter mapping.

### Language parameter

Define the preferred language of address elements in the result. Without a preferred language, the HERE geocoder will return results in an official country language or in a regional primary language. Language code must be provided according to RFC 4647 standard.

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
