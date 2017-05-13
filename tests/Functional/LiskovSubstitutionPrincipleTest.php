<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace geocoder\tests\Functional;

use Geocoder\Collection;
use Geocoder\Location;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Bounds;
use Geocoder\Model\Coordinates;
use Geocoder\Model\Country;
use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Provider\BingMaps\BingMaps;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;
use Geocoder\Provider\GeoIPs\GeoIPs;
use Geocoder\Provider\GeoPlugin\GeoPlugin;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Provider\OpenCage\OpenCage;
use Geocoder\Provider\Provider;
use Geocoder\Tests\CachedResponseClient;
use Http\Client\HttpClient;
use Http\Client\Curl\Client as HttplugClient;

/**
 * Test all adapters and make sure they provide the similar result.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class LiskovSubstitutionPrincipleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param Provider $provider
     * @dataProvider getWorldWideProvider
     */
    public function testGeocodeWorldWideProvider(Provider $provider)
    {
        /*
         * Find the address for the British prime minister
         */
        $query = GeocodeQuery::create('10 Downing St, London, UK')->withLocale('en');
        $result = $provider->geocodeQuery($query);
        $this->assertWellFormattedResult($result);

        // Check Downing Street
        $location = $result->first();
        $this->assertEquals(51.5033, $location->getCoordinates()->getLatitude(), 'Latitude should be in London', 0.1);
        $this->assertEquals(-0.1276, $location->getCoordinates()->getLongitude(), 'Longitude should be in London', 0.1);
        $this->assertContains('Downing St', $location->getStreetName(), 'Street name should contain "Downing St"');
        $this->assertContains('10', $location->getStreetNumber(), 'Street number should contain "10"');

        /*
         * Find the a good French/Canadian address
         */
        $query = GeocodeQuery::create('367 Rue St-Paul E, Montréal, Québec')->withLocale('en');
        $result = $provider->geocodeQuery($query);
        $this->assertWellFormattedResult($result);

        // Check Downing Street
        $location = $result->first();
        $this->assertEquals(45.5094, $location->getCoordinates()->getLatitude(), 'Latitude should be in Québec', 0.2);
        $this->assertEquals(-73.5516, $location->getCoordinates()->getLongitude(), 'Longitude should be in Québec', 0.5);
        $this->assertContains('Paul', $location->getStreetName(), 'Street name should contain "Saint Paul"');
        $this->assertContains('367', $location->getStreetNumber(), 'Street number should contain "367"');

        /*
         * Test other results are well formatted
         */
        // This will normally generate many results
        $query = GeocodeQuery::create('Paris')->withLocale('en');
        $this->assertWellFormattedResult($provider->geocodeQuery($query));
    }

    /**
     * @param Provider $provider
     * @dataProvider getWorldWideProvider
     */
    public function testReverseWorldWideProvider(Provider $provider)
    {
        // Cheops pyramid
        $this->assertWellFormattedResult($provider->reverseQuery(ReverseQuery::fromCoordinates(29.979216, 31.134277)->withLocale('en')));

        // Close to the white house
        $this->assertWellFormattedResult($provider->reverseQuery(ReverseQuery::fromCoordinates(38.900206, -77.036991)->withLocale('en')));
    }

    /**
     * @param Provider $provider
     * @dataProvider getWorldWideProvider
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testNoResult(Provider $provider)
    {
        $provider->geocodeQuery(GeocodeQuery::create('abcdef, ghijkl, mnopqrs')->withLocale('en'));
    }

    /**
     * @param Provider $provider
     * @dataProvider getWorldWideProvider
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testNoResultReverse(Provider $provider)
    {
        // Out side Hawaii in Pacific ocean
        $provider->reverseQuery(ReverseQuery::fromCoordinates(25.388300, 179.861719)->withLocale('en'));
    }

    /**
     * @param Provider $provider
     * @dataProvider getIpAddressProvider
     */
    public function testIpProvider(Provider $provider)
    {
        // Google DNS
        $this->assertWellFormattedResult($provider->geocodeQuery(GeocodeQuery::create('8.8.8.8')->withLocale('en')));
    }

    /**
     * The providers that support addresses world wide.
     *
     * @return array
     */
    public function getWorldWideProvider()
    {
        return [
            [new GoogleMaps($this->getAdapter($_SERVER['GOOGLE_GEOCODING_KEY']), null, $_SERVER['GOOGLE_GEOCODING_KEY'])],
            [new BingMaps($this->getAdapter($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY'])],
            //[new MapQuest($this->getAdapter($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY'])],
            //[new Geonames($this->getAdapter($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME'])],
            //[new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY'])],
            [new OpenCage($this->getAdapter($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY'])],
            //[new Mapzen($this->getAdapter($_SERVER['MAPZEN_API_KEY']), $_SERVER['MAPZEN_API_KEY'])],
            //[new ArcGISOnline($this->getAdapter())],
            //[new Yandex($this->getAdapter())],
            [Nominatim::withOpenStreetMapServer($this->getAdapter())],
            //[new MaxMind($this->getAdapter($_SERVER['MAXMIND_API_KEY']), $_SERVER['MAXMIND_API_KEY'])],
            //[new IpInfoDb($this->getAdapter($_SERVER['IPINFODB_API_KEY']), $_SERVER['IPINFODB_API_KEY'])],
        ];
    }

    /**
     * The providers that support IP addresses.
     *
     * @return array
     */
    public function getIpAddressProvider()
    {
        return [
            [new GeoIPs($this->getAdapter($_SERVER['GEOIPS_API_KEY']), $_SERVER['GEOIPS_API_KEY'])],
            [new GeoPlugin($this->getAdapter())],
            [new FreeGeoIp($this->getAdapter())],
            [Nominatim::withOpenStreetMapServer($this->getAdapter(), 'en')],
            //[new MaxMind($this->getAdapter($_SERVER['MAXMIND_API_KEY']), $_SERVER['MAXMIND_API_KEY'])],
            //[new IpInfoDb($this->getAdapter($_SERVER['IPINFODB_API_KEY']), $_SERVER['IPINFODB_API_KEY'])],
        ];
    }

    /**
     * @return HttpClient
     */
    protected function getAdapter($apiKey = null)
    {
        return new CachedResponseClient(
            new HttplugClient(),
            isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES'],
            $apiKey
        );
    }

    /**
     * Make sure that a result for a Geocoder is well formatted. Be aware that even
     * a Location with no data may be well formatted.
     *
     * @param $result
     */
    private function assertWellFormattedResult(Collection $result)
    {
        $this->assertInstanceOf(
            Collection::class,
            $result,
            'The result must be an instance of a Geocoder\Collection'
        );

        $this->assertNotEmpty($result, 'Geocoder\Exception should never be empty. A NoResult exception should be thrown.');

        /** @var Location $location */
        foreach ($result as $location) {
            $this->assertInstanceOf(
                Location::class,
                $location,
                'All items in Geocoder\Collection must implement Geocoder\Location'
            );

            $this->assertInstanceOf(
                AdminLevelCollection::class,
                $location->getAdminLevels(),
                'Location::getAdminLevels MUST always return a AdminLevelCollection'
            );
            $arrayData = $location->toArray();
            $this->assertTrue(is_array($arrayData), 'Location::toArray MUST return an array.');
            $this->assertNotEmpty($arrayData, 'Location::toArray cannot be empty.');

            // Verify coordinates
            if (null !== $coords = $location->getCoordinates()) {
                $this->assertInstanceOf(
                    Coordinates::class,
                    $coords,
                    'Location::getCoordinates MUST always return a Coordinates or null'
                );

                // Using "assertNotEmpty" means that we can not have test code where coordinates is on equator or long = 0
                $this->assertNotEmpty($coords->getLatitude(), 'If coordinate object exists it cannot have an empty latitude.');
                $this->assertNotEmpty($coords->getLongitude(), 'If coordinate object exists it cannot have an empty longitude.');
            }

            // Verify bounds
            if (null !== $bounds = $location->getBounds()) {
                $this->assertInstanceOf(
                    Bounds::class,
                    $bounds,
                    'Location::getBounds MUST always return a Bounds or null'
                );

                // Using "assertNotEmpty" means that we can not have test code where coordinates is on equator or long = 0
                $this->assertNotEmpty($bounds->getSouth(), 'If bounds object exists it cannot have an empty values.');
                $this->assertNotEmpty($bounds->getWest(), 'If bounds object exists it cannot have an empty values.');
                $this->assertNotEmpty($bounds->getNorth(), 'If bounds object exists it cannot have an empty values.');
                $this->assertNotEmpty($bounds->getEast(), 'If bounds object exists it cannot have an empty values.');
            }

            // Check country
            if (null !== $country = $location->getCountry()) {
                $this->assertInstanceOf(
                    Country::class,
                    $country,
                    'Location::getCountry MUST always return a Country or null'
                );
                $this->assertFalse(null === $country->getCode() && null === $country->getName(), 'Both code and name cannot be empty');

                if (null !== $country->getCode()) {
                    $this->assertNotEmpty(
                        $location->getCountry()->getCode(),
                        'The Country should not have an empty code.'
                    );
                }

                if (null !== $country->getName()) {
                    $this->assertNotEmpty(
                        $location->getCountry()->getName(),
                        'The Country should not have an empty name.'
                    );
                }
            }
        }
    }

    /**
     * Assert contains or null.
     */
    public static function assertContains(
        $needle,
        $haystack,
        $message = '',
        $ignoreCase = false,
        $checkForObjectIdentity = true,
        $checkForNonObjectIdentity = false
    ) {
        if ($haystack === null) {
            return;
        }

        parent::assertContains(
            $needle,
            $haystack,
            $message,
            $ignoreCase,
            $checkForObjectIdentity,
            $checkForNonObjectIdentity
        );
    }
}
