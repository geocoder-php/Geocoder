# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 5.8.0

### Added

- Add support for PHP Geocoder 5

## 5.7.0

### Added

- Add support for PHP 8.2
- Add support for address neighbourhood property

## 5.6.0

### Added

- Add support for PHP 8.1

### Removed

- Drop support for PHP 7.3

### Changed

- Migrate from PHP-HTTP to PSR-18 client

## 5.5.1

### Changed

- Force query argument separator and encoding type

## 5.5.0

### Added

- Add GitHub Actions workflow
- Add support for optional address fields and extra tags

## 5.4.0

### Added

- Add support for PHP 8.0

### Removed

- Drop support for PHP 7.2

### Changed

- Upgrade PHPUnit to version 9

## 5.3.0

### Added

- Added support for address `quarter` property

## 5.2.0

### Fixed

- Fix issue when `country` property is not set

### Removed

- Drop support for PHP < 7.2

## 5.1.1

### Fixed

- Fixed issue with result without `osm_id` or `osm_type`

## 5.1.0

### Added

- Add `countrycodes` geocoding query parameter (via `withData()` function) : Limit search results to a specific country (or a list of countries).
- Add `viewbox` geocoding query parameter (via `withData()` function) : The preferred area to find search results.
- Add `bounded` geocoding query parameter (via `withData()` function) : Restrict the results to only items contained with the viewbox (see above).

### Changed

- Switch from **XML** format to **JSON (v2)** format (see <https://wiki.openstreetmap.org/wiki/Nominatim>).

## 5.0.0

### Added

- Add User-Agent and Referer parameters to the constructor to comply to [Nominatim Usage Policy](https://operations.osmfoundation.org/policies/nominatim/).

### Removed

- Removed lookup by IP. Nominatim server never supported this feature. PHP module returned no/empty results for any IP. Now it returns `UnsupportedOperation` exception.

## 4.1.0

### Added

- Added `NominatimAddress`.

## 4.0.0

First release of this library.
