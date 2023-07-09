# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

## 4.6.0

### Removed

- Drop support for PHP 7.3

## 4.5.0

### Added

- Add support for PHP 8.1

### Changed

- Replace `empty()` by more strict checks

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

## 4.2.2

### Changed

- GeoJson dumper implements Dumper
- Excpetion interface extends Throwable

### Fixed

- Fix building ProviderNotRegistered exception message

## 4.2.1

### Fixed

- Bug in `AddressBuilder` where same expression is compare twice

## 4.2.0

### Added

- Add `Coordinates::toArray`

### Fixed

- Bug in `StatefulGeocoder` where different locale or bounds did not have any effect.

## 4.1.0

### Changed

- Make sure a `Country` never will be empty of data.

## 4.0.0

No changes since Beta 5.

## 4.0.0 - Beta 5

### Changed

- `GeocodeQuery::withTest` was renamed to `GeocodeQuery::withText`

## 4.0.0 - Beta 4

### Added

- Add `GeocodeQuery::withText` and `ReverseQuery::withCoordinates`.
- Create interface for GeocodeQuery and ReverseQuery

## 4.0.0 - Beta 3

### Added

- The constructor of `ProvierAggregator` will accept a callable that can decide what providers should be used for a specific query.

### Changed

- `ProvierAggregator::getProvider` is now private
- `ProvierAggregator::limit` was removed
- `ProvierAggregator::getLimit` was removed
- `ProvierAggregator::__constructor` changed the order of the parameters.
- `ProvierAggregator` is not final.

## 4.0.0 - Beta 2

### Added

- PHP7 type hints.
- `AbstractArrayDumper` and `AbstractDumper`
- `LogicException` and `OutOfBounds`
- `GeocodeQuery::__toString` and `ReverseQuery::__toString`

### Changed

- All Dumpers are now final.
- All Exceptions are now final.
- `AddressCollection` is now final.
- `ProviderAggregator` is now final.
- `StatefulGeocoder` is now final.
- `TimedGeocoder` is now final.
- `ProviderAggregator::getName()` will return "provider_aggregator"
- `TimedGeocoder::getName()` will return "timed_geocoder"

## 4.0.0 - Beta1

First release of this library.
