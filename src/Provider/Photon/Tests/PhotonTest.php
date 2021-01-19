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
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Photon\Photon;

class PhotonTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Photon provider does not support IP addresses.');

        $provider = Photon::withKomootServer($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Photon provider does not support IP addresses.');

        $provider = Photon::withKomootServer($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv6()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Photon provider does not support IP addresses.');

        $provider = Photon::withKomootServer($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function testGeocodeQuery()
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8631927, $result->getCoordinates()->getLatitude(), '', 0.00001);
        $this->assertEquals(2.3890894, $result->getCoordinates()->getLongitude(), '', 0.00001);
        $this->assertEquals('10', $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('75020', $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry());

        $this->assertEquals(1988097192, $result->getOSMId());
        $this->assertEquals('N', $result->getOSMType());
        $this->assertEquals('place', $result->getOSMTag()->key);
        $this->assertEquals('house', $result->getOSMTag()->value);
    }

    public function testGeocodeQueryWithNamedResult()
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Sherlock Holmes Museum, 221B Baker St, London, England'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();

        $this->assertEquals('The Sherlock Holmes Museum and shop', $result->getName());
    }

    public function testGeocodeQueryWithLatAndLon()
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $geocodeQuery = GeocodeQuery::create('Paris')
            ->withData('lat', 33.6625)
            ->withData('lon', -95.547778);
        $results = $provider->geocodeQuery($geocodeQuery);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertEquals('United States', $result->getCountry());
    }

    public function testReverseQuery()
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(52, 10));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.9982968, $result->getCoordinates()->getLatitude(), '', 0.00001);
        $this->assertEquals(9.998645, $result->getCoordinates()->getLongitude(), '', 0.00001);
        $this->assertEquals('31195', $result->getPostalCode());
        $this->assertEquals('Lamspringe', $result->getLocality());
        $this->assertEquals('Deutschland', $result->getCountry());

        $this->assertEquals(693697564, $result->getOSMId());
        $this->assertEquals('N', $result->getOSMType());
        $this->assertEquals('tourism', $result->getOSMTag()->key);
        $this->assertEquals('information', $result->getOSMTag()->value);
    }
}
