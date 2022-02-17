# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 1.3.0

### Added

- Add support for PHP 8.0

### Removed

- Drop support for PHP 7.2

### Changed

- Upgrade PHPUnit to version 9

## 1.2.0

### Added

- Adds support for reverse query `Nearby` mode (rankby `prominence` + `radius`, or `distance` + `type/keyword/name`)

### Fixed

- reverse query w/ `Search` mode checked for `type/keyword/name`, but only `type` is allowed

## 1.1.0

### Removed

- Drop support for PHP < 7.2

## 1.0.1

### Fixed

- Check if value `open_now` in `opening_hours`

## 1.0.0

First release of this library.
