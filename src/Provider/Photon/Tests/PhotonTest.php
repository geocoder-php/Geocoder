<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Photon\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\Photon\Photon;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class PhotonTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Photon provider does not support IP addresses.');

        $provider = Photon::withKomootServer($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Photon provider does not support IP addresses.');

        $provider = Photon::withKomootServer($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Photon provider does not support IP addresses.');

        $provider = Photon::withKomootServer($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function testGeocodeQuery(): void
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Provider\Photon\Model\PhotonAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.8631927, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(2.3890894, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('10', $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('75020', $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        $this->assertEquals(1988097192, $result->getOSMId());
        $this->assertEquals('N', $result->getOSMType());
        $this->assertEquals('place', $result->getOSMTag()->key);
        $this->assertEquals('house', $result->getOSMTag()->value);
        $this->assertEquals('Île-de-France', $result->getState());
        $this->assertNull($result->getCounty());
        $this->assertEquals('Paris', $result->getDistrict());
    }

    public function testGeocodeQueryWithNamedResult(): void
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Sherlock Holmes Museum, 221B Baker St, London, England'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Provider\Photon\Model\PhotonAddress $result */
        $result = $results->first();

        $this->assertEquals('The Sherlock Holmes Museum and shop', $result->getName());
    }

    public function testReverseQuery(): void
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(52, 10));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Provider\Photon\Model\PhotonAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(51.9982968, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(9.998645, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('31195', $result->getPostalCode());
        $this->assertEquals('Lamspringe', $result->getLocality());
        $this->assertEquals('Deutschland', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        $this->assertEquals(693697564, $result->getOSMId());
        $this->assertEquals('N', $result->getOSMType());
        $this->assertEquals('tourism', $result->getOSMTag()->key);
        $this->assertEquals('information', $result->getOSMTag()->value);
        $this->assertEquals('Niedersachsen', $result->getState());
        $this->assertEquals('Landkreis Hildesheim', $result->getCounty());
        $this->assertEquals('Sehlem', $result->getDistrict());
    }
}
