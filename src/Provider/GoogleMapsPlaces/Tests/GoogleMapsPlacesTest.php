<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMapsPlaces\Tests;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\GoogleMapsPlaces\GoogleMapsPlaces;
use Geocoder\Provider\GoogleMapsPlaces\Model\GooglePlace;
use Geocoder\Provider\GoogleMapsPlaces\Model\GooglePlaceAutocomplete;
use Geocoder\Provider\GoogleMapsPlaces\Model\OpeningHours;
use Geocoder\Provider\GoogleMapsPlaces\Model\Photo;
use Geocoder\Provider\GoogleMapsPlaces\Model\PlusCode;
use Geocoder\Provider\GoogleMapsPlaces\Model\StructuredFormatting;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @author atymic <atymicq@gmail.com>
 */
class GoogleMapsPlacesTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        if (isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES']) {
            return __DIR__.'/.cached_responses';
        }

        return null;
    }

    public function testGetName()
    {
        $provider = new GoogleMapsPlaces($this->getMockedHttpClient(), 'key');
        $this->assertEquals('google_maps_places', $provider->getName());
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The GoogleMapsPlaces provider does not support IP addresses');

        $provider = new GoogleMapsPlaces($this->getMockedHttpClient(), 'key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The GoogleMapsPlaces provider does not support IP addresses');

        $provider = new GoogleMapsPlaces($this->getMockedHttpClient(), 'key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIp()
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The GoogleMapsPlaces provider does not support IP addresses');

        $provider = $this->getGoogleMapsProvider();
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithQuotaExceeded()
    {
        $this->expectException(QuotaExceeded::class);
        $this->expectExceptionMessage('Daily quota exceeded https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=10+avenue+Gambetta%2C+Paris%2C+France&inputtype=textquery&fields=formatted_address%2Cgeometry%2Cicon%2Cname%2Cpermanently_closed%2Cphotos%2Cplace_id%2Cplus_code%2Ctypes&key=key');

        $provider = new GoogleMapsPlaces($this->getMockedHttpClient('{"status":"OVER_QUERY_LIMIT"}'), 'key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodePlaceFindMode()
    {
        $provider = $this->getGoogleMapsProvider();

        $query = GeocodeQuery::create('Museum of Contemporary Art Australia');

        $results = $provider->geocodeQuery($query);
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertInstanceOf(GooglePlace::class, $result);

        $this->assertSame('ChIJ68aBlEKuEmsRHUA9oME5Zh0', $result->getId());
        $this->assertSame('https://maps.gstatic.com/mapfiles/place_api/icons/museum-71.png', $result->getIcon());
        $this->assertInstanceOf(PlusCode::class, $result->getPlusCode());
        $this->assertContainsOnlyInstancesOf(Photo::class, $result->getPhotos());

        $this->assertSame([
            'art_gallery',
            'museum',
            'cafe',
            'food',
            'store',
            'point_of_interest',
            'establishment',
        ], $result->getType());
    }

    public function testGeocodePlaceSearchMode()
    {
        $provider = $this->getGoogleMapsProvider();

        $query = GeocodeQuery::create('bar in sydney')
            ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_SEARCH);

        $results = $provider->geocodeQuery($query);
        $this->assertCount(20, $results);

        /** @var GooglePlace $resultOne */
        $resultOne = $results->first();

        $this->assertInstanceOf(GooglePlace::class, $resultOne);
        $this->assertSame('ChIJ3SS9Lj-uEmsRrVS7u1OEV_0', $resultOne->getId());
        $this->assertSame('Papa Gede\'s Bar', $resultOne->getName());
        $this->assertSame('348 Kent St, Sydney NSW 2000, Australia', $resultOne->getFormattedAddress());

        $this->assertSame([
            'bar',
            'restaurant',
            'food',
            'point_of_interest',
            'establishment',
        ], $resultOne->getType());

        $this->assertSame('https://maps.gstatic.com/mapfiles/place_api/icons/bar-71.png', $resultOne->getIcon());

        $this->assertInstanceOf(PlusCode::class, $resultOne->getPlusCode());
        $this->assertSame('4RRH46J3+3X', $resultOne->getPlusCode()->getGlobalCode());

        $this->assertContainsOnlyInstancesOf(Photo::class, $resultOne->getPhotos());

        $this->assertSame(2, $resultOne->getPriceLevel());
        $this->assertSame(4.7, $resultOne->getRating());

        $this->assertNull($resultOne->getFormattedPhoneNumber());
        $this->assertNull($resultOne->getInternationalPhoneNumber());
        $this->assertNull($resultOne->getWebsite());

        $this->assertInstanceOf(OpeningHours::class, $resultOne->getOpeningHours());

        $this->assertFalse($resultOne->isPermanentlyClosed());
    }

    public function testGeocodePlaceSearchAroundLocation()
    {
        $provider = $this->getGoogleMapsProvider();

        $query = GeocodeQuery::create('bar')
            ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_SEARCH)
            ->withData('location', '-32.926642, 151.783026')// Newcastle, NSW
            ->withData('radius', 100);

        $results = $provider->geocodeQuery($query);
        $this->assertCount(20, $results);

        /** @var GooglePlace $resultOne */
        $resultOne = $results->first();

        $this->assertInstanceOf(GooglePlace::class, $resultOne);
        $this->assertSame('ChIJ5_ZqMHsUc2sRHgfnw5D1FlY', $resultOne->getId());
        $this->assertSame('Reserve', $resultOne->getName());
        $this->assertSame('102 Hunter St, Newcastle NSW 2300, Australia', $resultOne->getFormattedAddress());
    }

    public function testGeocodePlaceAutocompleteMode()
    {
        $provider = $this->getGoogleMapsProvider();
        $query = GeocodeQuery::create('Paris')
            ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_AUTOCOMPLETE)
            ->withData('types', '(cities)')
            ->withData('components', 'country:fr')
            ->withData('language', 'fr')
        ;

        $results = $provider->geocodeQuery($query);
        $this->assertCount(5, $results);
        /**
         * @var GooglePlaceAutocomplete $result
         */
        $result = $results->first();
        $this->assertInstanceOf(GooglePlaceAutocomplete::class, $result);
        $this->assertSame('ChIJD7fiBh9u5kcRYJSMaMOCCwQ', $result->getId());
        $this->assertSame('Paris, France', $result->getDescription());
        $this->assertInstanceOf(StructuredFormatting::class, $result->getStructuredFormatting());
        $this->assertIsArray($result->getMatchedSubstrings());
        $this->assertIsArray($result->getTerms());
        $this->assertIsArray($result->getTypes());

        $this->assertSame(
            [['length' => 5, 'offset' => 0]],
            $result->getMatchedSubstrings()
        );
        $this->assertSame(
            [
                ['offset' => 0, 'value' => 'Paris'],
                ['offset' => 7, 'value' => 'France'],
            ],
            $result->getTerms()
        );
        $this->assertSame([
            'locality',
            'political',
            'geocode',
        ], $result->getTypes());
    }

    public function testReverseGeocodePlaceSearchWithoutType()
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('`type` is required to be set in the Query data for Reverse Geocoding when using search mode');

        $provider = $this->getGoogleMapsProvider();
        $query = ReverseQuery::fromCoordinates(-33.8865019, 151.2080413)
            ->withData('rankby', 'distance')
            ;

        $provider->reverseQuery($query);
    }

    public function testReverseGeocodePlaceSearch()
    {
        $provider = $this->getGoogleMapsProvider();

        $query = ReverseQuery::fromCoordinates(-33.892674, 151.200727)
            // ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_SEARCH) // =default
            ->withData('type', 'bar')
            ;

        $results = $provider->reverseQuery($query);

        $this->assertCount(20, $results);

        /** @var GooglePlace $resultOne */
        $resultOne = $results->first();

        $this->assertInstanceOf(GooglePlace::class, $resultOne);
        $this->assertSame('ChIJ3Y3vQdqxEmsRTvCcbZnsYJ8', $resultOne->getId());
        $this->assertSame('Arcadia', $resultOne->getName());
        $this->assertSame('7 Cope St, Redfern NSW 2016', $resultOne->getFormattedAddress());
    }

    public function testReverseGeocodePlaceNearbyDistanceWithoutKeyword()
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('keyword');

        $provider = $this->getGoogleMapsProvider();
        $query = ReverseQuery::fromCoordinates(-33.892674, 151.200727)
            ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_NEARBY)
            ->withData('rankby', 'distance')
            // ->withData('keyword', 'bar')
            ;

        $provider->reverseQuery($query);
    }

    public function testReverseGeocodePlaceNearbyDistance()
    {
        $provider = $this->getGoogleMapsProvider();

        $query = ReverseQuery::fromCoordinates(-33.892674, 151.200727)
            ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_NEARBY)
            ->withData('rankby', 'distance')
            ->withData('keyword', 'bar')
            ;

        $results = $provider->reverseQuery($query);

        $this->assertCount(20, $results);

        /** @var GooglePlace $resultOne */
        $resultOne = $results->first();

        $this->assertInstanceOf(GooglePlace::class, $resultOne);
        $this->assertSame('ChIJ3Y3vQdqxEmsRTvCcbZnsYJ8', $resultOne->getId());
        $this->assertSame('Arcadia', $resultOne->getName());
        $this->assertSame('7 Cope St, Redfern', $resultOne->getVicinity());
        // $this->assertNull($resultOne->getFormattedAddress());
        // formatted address not available with NEARBY endpoint ()
    }

    public function testReverseGeocodePlaceNearbyProminenceWithoutRadius()
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('radius');

        $provider = $this->getGoogleMapsProvider();
        $query = ReverseQuery::fromCoordinates(-33.892674, 151.200727)
            ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_NEARBY)
            // ->withData('rankby', 'prominence')
            // ->withData('radius', 500)
            ;

        $provider->reverseQuery($query);
    }

    public function testReverseGeocodePlaceNearbyProminence()
    {
        $provider = $this->getGoogleMapsProvider();

        $query = ReverseQuery::fromCoordinates(-33.892674, 151.200727)
                ->withData('mode', GoogleMapsPlaces::GEOCODE_MODE_NEARBY)
                //->withData('rankby', 'prominence'); // =default
                ->withData('radius', 500)
                ;

        $results = $provider->reverseQuery($query);
        $this->assertCount(20, $results);

        /** @var GooglePlace $resultOne */
        $resultOne = $results->first();

        $this->assertInstanceOf(GooglePlace::class, $resultOne);
        $this->assertSame('ChIJP3Sa8ziYEmsRUKgyFmh9AQM', $resultOne->getId()); // Sydney
    }

    public function testReverseGeocodePlaceSearchWithEmptyOpeningHours()
    {
        $provider = $this->getGoogleMapsProvider();

        $query = ReverseQuery::fromCoordinates(51.0572773, 13.7763207)
            ->withData('type', 'transit_station')
            ;

        $results = $provider->reverseQuery($query);
        $this->assertCount(20, $results);

        $this->markTestIncomplete('Test is giving irregular results. Marking incomplete for now.');

        /** @var GooglePlace $resultOne */
        $resultOne = $results->get(13);
        $this->assertNull($resultOne->getOpeningHours()->isOpenNow());
        // sometimes giving: Error: Call to a member function isOpenNow() on null

        /** @var GooglePlace $resultTwo */
        $resultTwo = $results->first();
        $this->assertNull($resultTwo->getOpeningHours());
        // sometimes giving: Failed asserting that Object ['openNow' => null, 'periods' => [], 'weekdayText' => []] is null
    }

    private function getGoogleMapsProvider(): GoogleMapsPlaces
    {
        if (!isset($_SERVER['GOOGLE_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLE_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new GoogleMapsPlaces(
            $this->getHttpClient($_SERVER['GOOGLE_GEOCODING_KEY']),
            $_SERVER['GOOGLE_GEOCODING_KEY']
        );

        return $provider;
    }
}
