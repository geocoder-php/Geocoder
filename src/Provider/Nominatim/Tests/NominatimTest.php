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
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Nominatim provider does not support IP addresses.');

        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Nominatim provider does not support IP addresses.');

        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv6()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Nominatim provider does not support IP addresses.');

        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function testReverseWithCoordinatesGetsError()
    {
        $errorJSON = '{"error":"Unable to geocode"}';

        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient($errorJSON), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(-80.000000, -170.000000));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGetNodeStreetName()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
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
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $results = $provider->geocodeQuery(GeocodeQuery::create('35 avenue jean de bologne 1020 bruxelles'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(50.896344, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(4.3605984, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('35', $result->getStreetNumber());
        $this->assertEquals('Avenue Jean de Bologne - Jean de Bolognelaan', $result->getStreetName());
        $this->assertEquals('1020', $result->getPostalCode());
        $this->assertEquals('Ville de Bruxelles - Stad Brussel', $result->getLocality());
        $this->assertEquals('Heysel - Heizel', $result->getSubLocality());
        $this->assertEquals('BE', $result->getCountry()->getCode());

        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright', $result->getAttribution());
        $this->assertEquals('building', $result->getCategory());
        $this->assertEquals('35, Avenue Jean de Bologne - Jean de Bolognelaan, Heysel - Heizel, Laeken / Laken, Ville de Bruxelles - Stad Brussel, Brussel-Hoofdstad - Bruxelles-Capitale, Région de Bruxelles-Capitale - Brussels Hoofdstedelijk Gewest, 1020, België / Belgique / Belgien', $result->getDisplayName());
        $this->assertEquals(220754533, $result->getOSMId());
        $this->assertEquals('way', $result->getOSMType());
        $this->assertEquals('yes', $result->getType());
    }

    public function testGeocodeWithRealAddressThatReturnsOptionalQuarter()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $results = $provider->geocodeQuery(GeocodeQuery::create('woronicza 17, warszawa, polska'));

        $this->assertCount(1, $results);

        /* @var \Geocoder\Provider\Nominatim\Model\NominatimAddress $result */
        $this->assertEquals('Służewiec', $results->first()->getQuarter());
    }

    public function testGeocodeWithCountrycodes()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');

        $query = GeocodeQuery::create('palais royal')
            ->withData('countrycodes', 'be');

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertGreaterThanOrEqual(1, $results->count());

        /** @var \Geocoder\Model\Address $result */
        foreach ($results as $result) {
            $this->assertEquals('BE', $result->getCountry()->getCode());
        }
    }

    public function testGeocodeWithViewbox()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');

        $query = GeocodeQuery::create('35 avenue jean de bologne 1020 bruxelles')
            ->withData('viewbox', [4.3539793798, 50.8934444743, 4.3638069937, 50.9000218934])
            ->withData('bounded', true);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(50.896344, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(4.3605984, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('35', $result->getStreetNumber());
        $this->assertEquals('Avenue Jean de Bologne - Jean de Bolognelaan', $result->getStreetName());
        $this->assertEquals('1020', $result->getPostalCode());
        $this->assertEquals('Ville de Bruxelles - Stad Brussel', $result->getLocality());
        $this->assertEquals('Heysel - Heizel', $result->getSubLocality());
        $this->assertEquals('BE', $result->getCountry()->getCode());

        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright', $result->getAttribution());
        $this->assertEquals('building', $result->getCategory());
        $this->assertEquals('35, Avenue Jean de Bologne - Jean de Bolognelaan, Heysel - Heizel, Laeken / Laken, Ville de Bruxelles - Stad Brussel, Brussel-Hoofdstad - Bruxelles-Capitale, Région de Bruxelles-Capitale - Brussels Hoofdstedelijk Gewest, 1020, België / Belgique / Belgien', $result->getDisplayName());
        $this->assertEquals(220754533, $result->getOSMId());
        $this->assertEquals('way', $result->getOSMType());
        $this->assertEquals('yes', $result->getType());
    }

    public function testGeocodeWithStructuredRequest()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');

        $query = GeocodeQuery::create(' ')->withData('state', 'Nevada');

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Nevada, United States', $result->getDisplayName());
    }

    public function testGeocodeNoOSMId()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $results = $provider->geocodeQuery(GeocodeQuery::create('90210,United States'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('90210', $result->getPostalCode());
        $this->assertEquals('US', $result->getCountry()->getCode());

        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright', $result->getAttribution());
        $this->assertEquals('place', $result->getCategory());
        $this->assertEquals('postcode', $result->getType());
        $this->assertEquals(null, $result->getOSMId());
        $this->assertEquals(null, $result->getOSMType());
    }

    public function testGeocodeNoCountry()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $results = $provider->geocodeQuery(GeocodeQuery::create('Italia'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright', $result->getAttribution());

        $this->assertEquals('Italia', $result->getDisplayName());
        $this->assertEquals('waterway', $result->getCategory());
        $this->assertEquals('62194430', $result->getOSMId());
        $this->assertEquals('way', $result->getOSMType());
        $this->assertEquals(null, $result->getCountry());
    }
}
