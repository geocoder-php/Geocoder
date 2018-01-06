<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Nominatim\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Nominatim\Nominatim;

class NominatimTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Nominatim provider does not support IPv6 addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Nominatim provider does not support IPv6 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithAddressGetsEmptyContent()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient('<foo></foo>'));
        $provider->geocodeQuery(GeocodeQuery::create('Läntinen Pitkäkatu 35, Turku'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithAddressGetsEmptyXML()
    {
        $emptyXML = <<<'XML'
<?xml version="1.0" encoding="utf-8"?><searchresults_empty></searchresults_empty>
XML;
        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient($emptyXML));
        $provider->geocodeQuery(GeocodeQuery::create('Läntinen Pitkäkatu 35, Turku'));
    }

    public function testReverseWithCoordinatesGetsError()
    {
        $errorXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<reversegeocode querystring='format=xml&amp;lat=-80.000000&amp;lon=-170.000000&amp;addressdetails=1'>
    <error>Unable to geocode</error>
</reversegeocode>
XML;
        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient($errorXml));
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(-80.000000, -170.000000));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGetNodeStreetName()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86, 2.35));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Rue Quincampoix', $result->getStreetName());
    }

    public function testGeocodeWithRealAddress()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('35 avenue jean de bologne 1020 bruxelles'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.896344, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(4.3605984, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Avenue Jean de Bologne - Jean de Bolognelaan', $result->getStreetName());
        $this->assertEquals('1020', $result->getPostalCode());
        $this->assertEquals('Ville de Bruxelles - Stad Brussel', $result->getLocality());
        $this->assertEquals('Heysel - Heizel', $result->getSubLocality());
        $this->assertEquals('BE', $result->getCountry()->getCode());

        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. http://www.openstreetmap.org/copyright', $result->getAttribution());
        $this->assertEquals('building', $result->getClass());
        $this->assertEquals('35, Avenue Jean de Bologne - Jean de Bolognelaan, Heysel - Heizel, Laeken / Laken, Ville de Bruxelles - Stad Brussel, Brussel-Hoofdstad - Bruxelles-Capitale, Région de Bruxelles-Capitale - Brussels Hoofdstedelijk Gewest, 1020, België / Belgique / Belgien', $result->getDisplayName());
        $this->assertEquals(220754533, $result->getOSMId());
        $this->assertEquals('way', $result->getOSMType());
        $this->assertEquals('yes', $result->getType());
    }
}
