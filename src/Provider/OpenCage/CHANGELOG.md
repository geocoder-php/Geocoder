# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 4.8.0

### Added

- Add support for PHP Geocoder 5

## 4.7.0

### Added

-  Add support for PHP 8.2, 8.3, and 8.4
-  Add support for confidence

### Removed

- Drop support for PHP 7.4

## 4.6.0

### Added

- Add support for PHP 8.1
- Add GitHub Actions workflow

### Removed

- Drop support for PHP 7.3

### Changed

- Migrate from PHP-HTTP to PSR-18 client

## 4.5.0

### Added

- Add support for PHP 8.0

### Removed

- Drop support for PHP 7.2

### Changed

- Upgrade PHPUnit to version 9

## 4.4.0

### Removed

- Drop support for PHP < 7.2

## 4.3.0

### Added

- Support for parameters to reduce ambiguity with [*ambiguous results*](https://opencagedata.com/api#ambiguous-results)

## 4.2.0

### Changed

- Improve StreetName, Locality, and SubLocality mapping

## 4.1.0

### Added

- Added `OpenCageAddress` model.
  - Added `MGRS`
  - Added `Maidenhead`
  - Added `geohash`
  - Added `what3words`
  - Added formatted address

## 4.0.0

First release of this library.
