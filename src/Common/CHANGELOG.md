# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

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
