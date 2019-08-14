# Change Log

The change log describes what is "Added", "Removed", "Changed" or "Fixed" between each release.

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
