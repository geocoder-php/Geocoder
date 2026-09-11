# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 2.0.0

### Added

- Support for the Mapbox Geocoding API v6 (`/search/geocode/v6/forward` and `/search/geocode/v6/reverse`)
- v6 Structured Input: forward queries built from typed address fields via query data — `address_line1`, `address_number`, `street`, `block`, `place`, `region`, `postcode`, `locality`, `neighborhood`. When any of these is present, the query text is not sent.
- `Mapbox::TYPE_STREET` (new v6 type, added to `Mapbox::TYPES`)
- `Mapbox::TYPE_BLOCK` (Japan) and `Mapbox::TYPE_SECONDARY_ADDRESS` (US) constants (not part of `TYPES`: the API only accepts them in the corresponding regional context)
- `Mapbox::STRUCTURED_INPUT_FIELDS` constant
- Query data support: `autocomplete` (replaces `fuzzy_match`), `proximity` (`"lon,lat"` or `"ip"`), `worldview`
- New 4th constructor argument `bool $permanent` (v6 `permanent=true` result storage)
- `MapboxAddress::getMatchCode()` / `withMatchCode()` — v6 Smart Address Match object
- `MapboxAddress::getMatchConfidence()` / `withMatchConfidence()` — `exact`, `high`, `medium` or `low`
- `MapboxAddress::getAccuracy()` / `withAccuracy()` — `rooftop`, `parcel`, `point`, `interpolated`, `approximate` or `intersection`

### Removed

- Support for the Mapbox Geocoding API v5 (`/geocoding/v5/...`) — pin to `geocoder-php/mapbox-provider:^1.5` to stay on v5
- `Mapbox::GEOCODE_ENDPOINT_URL_SSL` and `Mapbox::REVERSE_ENDPOINT_URL_SSL` constants (replaced by `Mapbox::FORWARD_ENDPOINT_URL` / `Mapbox::REVERSE_ENDPOINT_URL`)
- `Mapbox::GEOCODING_MODE_PLACES`, `Mapbox::GEOCODING_MODE_PLACES_PERMANENT` and `Mapbox::GEOCODING_MODES` constants (v6 has no geocoding modes; use the `permanent` constructor argument)
- `Mapbox::TYPE_POI` and `Mapbox::TYPE_POI_LANDMARK` constants (v6 does not geocode POIs; passing them as `location_type` now yields a 422 error from the API)
- `fuzzy_match` query data (v6 has no fuzzy/typo matching; use `autocomplete` for prefix-style matching)

### Changed

- The 4th constructor argument is now `bool $permanent = false` (was `string $geocodingMode`); passing a mode string changes behavior or errors
- `Mapbox::TYPES` no longer contains the POI types and now contains `street`
- Forward requests send the search text as the `q` query parameter instead of in the URL path
- Reverse requests send `longitude` / `latitude` query parameters (was `{lon},{lat}` in the path)
- `MapboxAddress::getId()` returns the v6 `mapbox_id` value (format changed, e.g. `dXJu...` instead of `address.123`)
- `MapboxAddress::getResultType()` values follow the v6 `feature_type` vocabulary (`street` added, `poi`/`poi.landmark` gone)
- Reverse geocoding with a `limit` greater than 1 requires a single `location_type` value (v6 API constraint: multiple types + limit returns a 422 error)
- Forward `limit` is capped at 10 by the v6 API (higher values are silently clamped)
- Invalid access tokens are now answered with HTTP 401 by the API (v5 used 403); both map to `Geocoder\Exception\InvalidCredentials`
- Geocoded data reflects the v6 dataset: coordinates, results and admin-level codes may differ from v5

## 1.5.0

### Added

- Add support for PHP Geocoder 5

## 1.4.0

### Added

- Add support for PHP 8.1
- Add GitHub Actions workflow

### Removed

- Drop support for PHP 7.3

### Changed

- Migrate from PHP-HTTP to PSR-18 client

## 1.3.0

### Added

- Add support for PHP 8.0

### Removed

- Drop support for PHP 7.2

### Changed

- Upgrade PHPUnit to version 9

## 1.2.0

### Added

- Support for `fuzzyMatch` parameter

## 1.1.0

### Fixed

- Fix issue when country `short_code` is null

### Removed

- Drop support for PHP < 7.2

## 1.0.2

### Fixed

- Check if country `short_code` key is set before extracting it

## 1.0.1

### Fixed

- Fix the Bounds query builder format

## 1.0.0

First release of this library.
