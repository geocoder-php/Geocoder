# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

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
