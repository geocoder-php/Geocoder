# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 2.0.0

### Added

- Support for the Mapbox Geocoding API v6 (`/search/geocode/v6/forward` and `/search/geocode/v6/reverse`)
- Support for the v6 `autocomplete` parameter and the v6 Structured Input fields
- `Mapbox::TYPE_STREET` and `Mapbox::STRUCTURED_INPUT_FIELDS` constants
- `MapboxAddress::getMatchCode()`, `getMatchConfidence()` and `getAccuracy()` for the v6 response data

### Removed

- Support for the Mapbox Geocoding API v5 (pin to `^1.5` to keep using it)
- Support for the v5 `fuzzy_match` parameter (use `autocomplete` instead)
- `Mapbox::TYPE_POI` and `Mapbox::TYPE_POI_LANDMARK` constants (not available in v6)

### Changed

- The `mapbox.places-permanent` mode is now sent as the v6 `permanent` parameter
- Results carry the v6 `mapbox_id` format and v6 dataset values

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
