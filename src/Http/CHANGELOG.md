# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 4.6.0

- Drop support for PHP 7
- Make PSR-17 a first class citizen

## 4.5.0

### Added

- Add support for PHP 8.1
- Add GitHub Actions workflow

### Removed

- Drop support for PHP 7.3

### Changed

- Migrate from PHP-HTTP to PSR-18 client

## 4.4.0

### Added

- Add support for PHP 8.0

### Removed

- Drop support for PHP 7.2

### Changed

- Upgrade PHPUnit to version 9

## 4.3.0

### Removed

- Drop support for PHP < 7.2

## 4.2.0

## 4.1.0

### Changed

- Refactored `AbstractHttpProvider::getUrlContents` to split it up to different functions. We now
got `AbstractHttpProvider::getUrlContents`, `AbstractHttpProvider::getRequest` and `AbstractHttpProvider::getParsedResponse`.

## 4.0.0

No changes since beta 2.

## 4.0.0-beta2

- Removed `AbstractHttpProvider::setMessageFactory`.
- Removed `AbstractHttpProvider::getHttpClient`.
- Make sure we have a `MessageFactory` in the constructor of `AbstractHttpProvider`.

## 4.0.0-beta1

First release of this library.
