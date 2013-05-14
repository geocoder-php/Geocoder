CHANGELOG
=========

### 1.5.1 (????-??-??) ###

* Added: MaxMind's binary provider

### 1.5.0 (2013-05-03) ###

* Added: DefaultResultFactory and MultipleResultFactory classes - Fix #223
* Fixed: provider's tests
* Fixed: encoding for geoip provider
* fixed: inspection values
* Fixed: tests and enhanced test cover
* Fixed: branch-alias (composer)

### 1.4.0 (2013-03-16) ###

* Updated: doc with an exemple of the ChainProvider
* Updated: doc about result object
* Refactored: adapters and its tests
* Updated: readme for new ArcGIS Online Provider
* Added: ArcGIS Online provider
* Fixed: OpenStreetMaps test
* Updated: Set a custom result factory via a setter
* Fixed: compatibility php 5.3
* Fixed: Yandex test
* Added: TomTom Provider

### 1.3.0 (2013-03-04) ###

* Fixed: ipgeobase url
* Fixed: MapQuest test
* Added: BaiduProvider + test
* Fixed: OpenStreetMaps test
* Added: IpGeoBase-ru as a provider + test
* Fixed: travis-ci config
* Added: adress support for datascience
* Fixed: FreeGeoIp provider's tests
* Updated: documentation about Geocoder::using()

### 1.2.1 (2013-02-03) ###

* Updated: documentation - Google Maps Business and MaxMind providers
* Added: SSL support to Google Maps Business provider + test
* Added: SSL support to MaxMind provider
* Added: locale parameter test to Yahoo provider
* Updated: BingMaps provider has a locale parameter
* Added: Omni service + tests - thanks @lox
* Added: MaxMind provider support IPv6 + tests
* Added: City District in formatter
* Fixed: MaxMind provider - fix #183
* Updated: precise which api key works with MaxMind provider
* Fixed: test to make it compatible against different databse
* Added missing test
* Fixed REVERSE_ENDPOINT_URL
* Added tests for Geonames Provider
* Added Geonames Provider
* Added: AbstractResult class
* Fixed: Yandex provider's tests
* Add a ResultFactory to easily create ResultInterface instances

### 1.2.0 (2013-01-15) ###

* Fix SocketHttpAdapter which did not take care of query string
* Fix tests/CS
* Fix response interpretation in GeopIPs Provider considering error and ok response
* Update tests
* Change wording from 'splitted' to 'split'
* Fix response processing in GeoIPsProvider
* Added exception for status code = OVER_QUERY_LIMIT

### 1.1.6 (2013-01-08) ###

* Restore OIORestProvider tests - fix #169
* Skip OIORest tests

### 1.1.5 (2012-12-29) ###

* Add PHP 5.5 to travis-ci config
* Correct property reference.
* Option to use SSL when communicating with end point
* Fix some tests due to data changed
* Complete exemple's outputs in README
* Throws InvalidCredentialsException on invalid api key + test
* Added UnsupportedException to ProviderInterface

### 1.1.4 (2012-12-04) ###

* Fixed indentation in OIORestProvider
* Added countyCode to the Geocoded result object for county short name
* Added reverse geocoding to OIORestProvider + test
* fix tests
* fix code inspection
* make adapter and locale mutable within provider

### 1.1.3 (2012-11-17) ###

* Replace urlencode() by rawurlencode() in GoogleMapsProvider
* Removed redundant if statement
* Removed not reliable place_rank and limit result to one + test - fix #129
* Use sf2 coding standard
* Fixed some CS in providers
* Fix ArrayAccess methods in Geocoded class. Fix #150
* add failing test for mixed case array access
* Fix timezone in IpInfoDbProviderTest
* Adding MaxMindProvider + Tests
* Refactored providers result with array_merge + tests - fix #145
* Added: HttpException and ExtensionNotLoadedException
* Fix CS - start to use sf2 coding standard + closes #147
* Fixed: use identical comparison operator
* Fixed CS, logic and tests in some providers
* Use exception interface to respect convention
* Issue #81: Added Google Maps for Business provider

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
