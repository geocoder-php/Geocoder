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
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class NominatimTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Nominatim provider does not support IP addresses.');

        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Nominatim provider does not support IP addresses.');

        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Nominatim provider does not support IP addresses.');

        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function testReverseWithCoordinatesGetsError(): void
    {
        $errorJSON = '{"error":"Unable to geocode"}';

        $provider = Nominatim::withOpenStreetMapServer($this->getMockedHttpClient($errorJSON), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(-80.000000, -170.000000));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGetNodeStreetName(): void
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

    public function testGeocodeWithRealAddress(): void
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $results = $provider->geocodeQuery(GeocodeQuery::create('1 Place des Palais 1000 bruxelles'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Provider\Nominatim\Model\NominatimAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(50.8419916, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(4.361988, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('1', $result->getStreetNumber());
        $this->assertEquals('Place des Palais - Paleizenplein', $result->getStreetName());
        $this->assertEquals('1000', $result->getPostalCode());
        $this->assertEquals('Ville de Bruxelles - Stad Brussel', $result->getLocality());
        $this->assertEquals('Pentagone - Vijfhoek', $result->getSubLocality());
        $this->assertEquals('BE', $result->getCountry()->getCode());

        $details = $result->getDetails();
        $this->assertCount(4, $details);
        $this->assertArrayHasKey('city_district', $details);
        $this->assertEquals('Région de Bruxelles-Capitale - Brussels Hoofdstedelijk Gewest', $details['region']);
        $this->assertEquals('Bruxelles - Brussel', $details['city_district']);
        $this->assertEquals('Quartier Royal - Koninklijke Wijk', $details['neighbourhood']);
        $this->assertEquals('Palais Royal - Koninklijk Paleis', $details['tourism']);

        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright', $result->getAttribution());
        $this->assertEquals('tourism', $result->getCategory());
        $this->assertEquals('Palais Royal - Koninklijk Paleis, 1, Place des Palais - Paleizenplein, Quartier Royal - Koninklijke Wijk, Pentagone - Vijfhoek, Bruxelles - Brussel, Ville de Bruxelles - Stad Brussel, Brussel-Hoofdstad - Bruxelles-Capitale, Région de Bruxelles-Capitale - Brussels Hoofdstedelijk Gewest, 1000, België / Belgique / Belgien', $result->getDisplayName());
        $this->assertEquals(3299902, $result->getOSMId());
        $this->assertEquals('relation', $result->getOSMType());
        $this->assertEquals('attraction', $result->getType());
    }

    public function testGeocodeWithRealAddressThatReturnsOptionalQuarter(): void
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $results = $provider->geocodeQuery(GeocodeQuery::create('woronicza 17, warszawa, polska'));

        $this->assertCount(1, $results);

        /** @var \Geocoder\Provider\Nominatim\Model\NominatimAddress $result */
        $result = $results->first();

        $this->assertEquals('Ksawerów', $result->getQuarter());
    }

    public function testGeocodeWithRealAddressAndExtraTags(): void
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');

        $results = $provider->geocodeQuery(GeocodeQuery::create('Elbphilharmonie, Platz der deutschen Einheit 1, Hamburg'));
        $this->assertCount(1, $results);

        $results = $provider->geocodeQuery(GeocodeQuery::create('Elbphilharmonie, Platz der deutschen Einheit 1, Hamburg'));

        $this->assertCount(1, $results);

        /** @var \Geocoder\Provider\Nominatim\Model\NominatimAddress $result */
        $result = $results->first();
        $this->assertIsArray($result->getTags());
        $this->assertArrayHasKey('height', $result->getTags());
        $this->assertEquals('110 m', $result->getTags()['height']);
    }

    public function testGeocodeWithCountrycodes(): void
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

    public function testGeocodeWithViewbox(): void
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');

        $query = GeocodeQuery::create('1 Place des Palais 1000 bruxelles')
            ->withData('viewbox', [4.3574204633, 50.8390856095, 4.3680849263, 50.8443022723])
            ->withData('bounded', true);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Provider\Nominatim\Model\NominatimAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(50.8419916, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(4.361988, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('1', $result->getStreetNumber());
        $this->assertEquals('Place des Palais - Paleizenplein', $result->getStreetName());
        $this->assertEquals('1000', $result->getPostalCode());
        $this->assertEquals('Ville de Bruxelles - Stad Brussel', $result->getLocality());
        $this->assertEquals('Pentagone - Vijfhoek', $result->getSubLocality());
        $this->assertEquals('BE', $result->getCountry()->getCode());

        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright', $result->getAttribution());
        $this->assertEquals('tourism', $result->getCategory());
        $this->assertEquals('Palais Royal - Koninklijk Paleis, 1, Place des Palais - Paleizenplein, Quartier Royal - Koninklijke Wijk, Pentagone - Vijfhoek, Bruxelles - Brussel, Ville de Bruxelles - Stad Brussel, Brussel-Hoofdstad - Bruxelles-Capitale, Région de Bruxelles-Capitale - Brussels Hoofdstedelijk Gewest, 1000, België / Belgique / Belgien', $result->getDisplayName());
        $this->assertEquals(3299902, $result->getOSMId());
        $this->assertEquals('relation', $result->getOSMType());
        $this->assertEquals('attraction', $result->getType());
    }

    public function testGeocodeNoOSMId(): void
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $results = $provider->geocodeQuery(GeocodeQuery::create('90210,United States'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Provider\Nominatim\Model\NominatimAddress $result */
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

    public function testGeocodeNoCountry(): void
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $query = GeocodeQuery::create('Italia')
            ->withData('viewbox', [-58.541836, -62.181561, -58.41618, -62.141319])
            ->withData('bounded', true);
        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Provider\Nominatim\Model\NominatimAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright', $result->getAttribution());

        $this->assertEquals('Italia', $result->getDisplayName());
        $this->assertEquals('waterway', $result->getCategory());
        $this->assertEquals('62194430', $result->getOSMId());
        $this->assertEquals('way', $result->getOSMType());
        $this->assertEquals(null, $result->getCountry());
    }

    public function testGeocodeNeighbourhood(): void
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getHttpClient(), 'Geocoder PHP/Nominatim Provider/Nominatim Test');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(35.685939, 139.811695)->withLocale('en'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Provider\Nominatim\Model\NominatimAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright', $result->getAttribution());

        $this->assertEquals('Sarue 1-chome', $result->getNeighbourhood());
        $this->assertEquals('Japan', $result->getCountry());
    }
}
