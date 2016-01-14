CHANGELOG
=========

### 3.3.0 (2015-12-06)

* Added: timezone field for `FreeGeoIp` provider
* Added: guess method for street and suburb
* Added: use city, town village or hamlet as locality
* Added: return ISO 3166-2 region codes for the US and the rest of the world
* Fixed: `AdminLevelCollection::checkLevel()` (#468)

### 3.2.0 (2015-10-09)

* Added: add __toString() methods in AdminLevel and Country
* Added: __toString to StreamInterface mock
* Fixed: postal code for GeoIP2 provider
* Fixed: Make sure we check for an error in the response of the Yandex provider
* Fixed: emergency message "You need to specify the country and region codes."

### 3.1.0 (2015-08-13)

* Added: show more API error messages in `GoogleMaps`
* Fixed: require `http-adapter` `~0.8`
* Fixed: use `https` transport in Yandex provider (#431)
* Updated: documentation

### 3.0.0 (2015-04-20)

* Added: Introduce a `CollectionIsEmpty` exception, thrown when `AddressCollection` is empty. (Fix #412)
* Added: countrycode to Bing api calls
* Added: allow locale change with chain


### 3.0.0-alpha3 (2015-02-13)

* Added: `LocaleTrait` to reduce code duplication
* Added: introduce admin levels concept (see PR #398)
* Fixed: GeoIP2 results use underscore case
* Merged: branch '2.x' (see releases 2.8.1 and 2.8.2 for more details)

### 3.0.0-alpha2 (2014-12-22)

* Added: Introduce `AddressCollection` class
* Added: new documentation

### 3.0.0-alpha1 (2014-12-18)

* Added: `using()` method now throws an exception if provider not found
* Added: new Result classes (`Address`, `Bounds`, `Region`, `Country`, `County`, `Coordinates`)
* Added: new named exceptions
* Added: better exception messages
* Added: new HTTP layer thanks to `egeloen/http-adapter` library
* Added: `TimedGeocoder` implementation (works with StopWatch Symfony component)
* Added: `AbstractHttpProvider` (extending `AbstractProvider`)
* Added: provide a way to use IpInfoDB country precision
* Added: cached responses for BingMaps provider (tests)
* Added: cached reponse for GoogleMaps provider (tests)
* Added: `ProviderAggregator` (replacing the former `Geocoder` class)
* Added: ability to change providers locale at runtime
* Documentation: almost entirely rewritten
* Documentation: a note on versioning has been added
* Documentation: a Contributor Code of Conduct has been added for the entire
  Geocoder project
* Fixed: phpdoc, wording
* Fixed: providers are now highly configurable, even at runtime
* Fixed: `FreeGeoIp` property because of an API change
* Moved: IGN OpenLS provider to geocoder-extra (#339)
* Moved: OIORest provider to geocoder-extra (#336)
* Moved: GeoCoder.us provider to geocoder-extra (#338)
* Moved: GeoCoder.ca provider to geocoder-extra (#337)
* Moved: DataScienceToolkit provider to geocoder-extra (#340)
* Moved: Baidu provider to geocoder-extra (#341)
* Moved: IpGeoBase provider to geocoder-extra (#342)
* Renamed: properties such as:
    - city => locality
    - cityDistrict => subLocality
    - zipcode => postalCode
* Refactored: dumpers (remove Interface suffix, define a new method signature)
* Refactored: class names!
* Refactored: all providers now implement the `Geocoder` interface
* Refactored: exception messages are a bit more verbose
* Removed: `Provider`, `Dumper`, `Interface`, and `Exception` suffixes
* Removed: `autoload.php` file
* Removed: `OpenStreetMapsProvider` class (#335)
* Removed: HTTP adapters layer
* Removed: the `Geocoder` class does not exist anymore and has been replaced by
  the `ProviderAggregator` class

## 2.x

### 2.8.2 (2015-01-07)

* Fixed: encoding issue for `maxmind` provider

### 2.8.1 (2014-12-08)

* Fixed: freegeoip `zip_code` property because of an API change

### 2.8.0 (2014-10-03)

* Added: Allow for greater flexibility in setting curl parameters by passing in
  an array.
* Removed: GeoIP2 Omni support
* Removed: Cloudmade provider. They discontinued their service Apr/2014

### 2.7.0 (2014-09-14) ###

* Added: **new** provider: `OpenCageProvider`
  [geocoder.opencagedata.com](http://geocoder.opencagedata.com/)
* Fixed: `MapQuestProvider` when no relevant data are fetched
* Fixed: Ensure mb `formatString` is congruent to standard `ucwords()`
  functionality

### 2.6.0 (2014-09-02) ###

* Added: `region` and `regionCode` to GeoIP2Provider
* Added: throw `InvalidCredentialsException` with GoogleMapsBusinessProvider
* Added: support in MapQuestProvider for licensed endpoints (#318)
* Added: it is now possible to set the user agent in `CurlHttpAdapter`
* Fixed: google maps provider test
* Fixed: GeoPlugin returns 206 for anonymous proxies
* Removed: Google's sensor parameter
* Updated: Travis-CI config, doc, tests

### 2.5.0 (2014-05-16) ###

* Added: ability to set timeouts for `CurlHttpAdapter`
* Added: support for a Google Maps API key
* Added: premium support to `GeocoderCaProvider` + tests
* Added: test against `hhvm-nightly` on Travis-CI
* Updated: documentation

### 2.4.2 (2014-01-05) ###

* Fixed: GeoIPs provider expects a single location response due to recent API
  changes (#283).

### 2.4.1 (2013-12-16) ###

* Fixed: MapQuestProvider now works with API keys
* Fixed: ProviderInterface (bad argument name)

### 2.4.0 (2013-12-12) ###

* Added: MapQuest ApiKey required for open services
* Removed: unused class constants
* Removed: deprecated class that is not used anymore since 2.0.0

### 2.3.2 (2013-11-06) ###

* Fixed: GeoIPs provider stopped working due to api changes (#267)
* Fixed: installation guidelines

### 2.3.1 (2013-10-22) ###

* Fixed: GeoipProvider and MaxMindBinaryProvider now return a result set
* Added: fixEncoding() method in AbstractProvider (merged from 1.7)

### 2.3.0 (2013-10-17) ###

* Added: Reintroduce OpenStreetMapsProvider for BC purpose
* Fixed: Rename OpenStreetMaps => OpenStreetMap
         The `OpenStreetMapsProvider` is now **deprecated**, use the
         `OpenStreetMapProvider` instead.
* Fixed: replace extension_exists() by function_exists() for mbstring

### 2.2.0 (2013-09-16) ###

* Added: ChainNoResultException for aggregating ChainProvider exceptions.
* Added: CachedResponseAdapter for the test suite + cached responses
* Updated: composer installation to the current recommendation from
  http://getcomposer.org/download/

### 2.1.0 (2013-08-27) ###

* Added: Generic NominatimProvider
* Fixed: GoogleBusinessProvider "client_id" parameter back to just "client".
  This reverts commit 532345bbd41221d2460591844dfffb04194c66

### 2.0.1 (2013-08-08) ###

* Fixed: tests due to data changes
* Fixed: use OpenStreetMap pedestrian tag for street name if road tag is not available
* Updated: replace zendframework with zend-http

### 2.0.0 (2013-07-08) ###

* Fixed: tests due to data changes
* Added: more doc. Fix #242
* Added: setMaxResults method
* Added support for cities in Yandex Provider
* Fixed: GoogleMapsBusinessProvider provider (`client_id`)
* Refactored: providers to leverage ResultFactories - fix #232 - POTENTIAL BC BREAK

### 1.7.0 (2013-05-28) ###

* Updated: Geocoder now uses the official geoip library
* Added: LocaleAwareProviderInterface

### 1.6.0 (2013-05-22) ###

* Added: API key to MapQuestProvider
* Fixed: DataScienceToolkitProvider test

### 1.5.1 (2013-05-15) ###

* Fixed: BC break by reintroducing the ResultFactory class
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
