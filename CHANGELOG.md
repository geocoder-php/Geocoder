CHANGELOG
=========

### 1.1.2 (2012-11-13) ###

* Removed useless contructor
* Use short class name instead of FQCN
* Add GeoIPsProvider provider + Tests
* Add GeoPluginProvider + Tests
* Optimized and tested OSM reverse data error catching
* Fixed: YandexProvider test
* Check result element exists
* Added: YandexProvider, test and updated README
* Update README
* Rename SocketAdapter to SocketHttpAdapter to respect conventions
* Fixed: DataScienceToolkit provider and its test
* Fix PR #118
* fix cs adn ipv6
* unit test datasciencetoolkitprovider
* fixing broken tests case in a fr_FR localized environment : made tests now PHP's locale-aware
* Fix CS, remove var_dump
* fix typo
* Updated: tests bootstrap - check cURL and dependencies
* DataScienceToolkitProvider
* Added: IGNOpenLSProvider + tests
* Fixed: tests should only use cURL as HttpAdapter
* Fixed: casts in GeocoderUsProvider

### 1.1.1 (2012-10-23) ###

* Add more tests thanks to Antoine Corcy (@toin0u)
* Updated: README.md - need cURL to run unit tests
* Fix README + CS
* Updated: README
* Added: GeocoderUsProvider
* Added: GeocoderCaProvider
* Fix Geoip provider
* [Provider] fixed indentation.
* Fixed: sprintf type specifier in ChainProvider
* Fixed: tests
* Updated: providers tests getName()

### 1.1.0 (2012-10-16) ###

* Remove useless use statements
* fix tests due to a change in the exception message
* Fix README
* Fix tests
* Fix README
* Fix some tests
* Fix YahooProvider
* Fix OpenStreetMapsProvider
* Fix IpInfoDbProvider
* Fix HostIpProvider
* Fix GoogleMapsProvider
* Fix GeoipProvider
* Fix FreeGeoIpProvider
* Fix BingMapsProvider
* Add more named exceptions
* Fix CS
* Minor fixes
* Refactor the error handling - BC BREAK
* Bump version to 1.1.0-dev
* Updated: IPv4 and IPv6 informations to README.md
* Fixed: BingMapsProvider and CloudMadeProvider tests which need API keys
* Fixed: Guzzle v3.0.0 moved plugins from Guzzle\Http\Plugin to Guzzle\Plugin
* Added: IPv6 control to providers
* Added: Add filter_var checks on Address based only providers - BC BREAK


### 1.0.x ###

The most stable version of the Geocoder `1.0.x` versions is **1.0.14**
(2012-10-15). If you don't use it yet, it's recommended to upgrade. There won't
be any support on this set of versions as it's considered stable.
