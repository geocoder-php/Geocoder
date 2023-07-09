# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 4.7.1

### Fixed

- Fix issue with duplicated SubLocalityLevels

## 4.7.0

### Added

- Add support for PHP 8.1
- Add GitHub Actions workflow

### Removed

- Drop support for PHP 7.3

### Changed

- Migrate from PHP-HTTP to PSR-18 client

## 4.6.0

### Added

- Add support for PHP 8.0

### Removed

- Drop support for PHP 7.2

### Changed

- Upgrade PHPUnit to version 9

## 4.5.0

### Added

- Added `postal_code_suffix` field

### Removed

- Drop support for PHP < 7.2

## 4.4.0

### Added

- Added [partial_match](https://developers.google.com/maps/documentation/geocoding/intro#Results)
  > `partial_match` indicates that the geocoder did not return an exact match for the original request, though it was able to match part of the requested address. You may wish to examine the original request for misspellings and/or an incomplete address.

### Fixed

- Fix "*Administrative level X is defined twice*" issue

## 4.3.0

### Added

- Added [component filtering](https://developers.google.com/maps/documentation/geocoding/intro#ComponentFiltering).

## 4.2.0

### Added

- Added the `$channel` constructor parameter.

## 4.1.0

### Added

- Support for `SubLocality`.

## 4.0.0

First release of this library.
