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

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Photon provider does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = Photon::withKomootServer($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Photon provider does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = Photon::withKomootServer($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Photon provider does not support IP addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function testGeocodeQuery()
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('35 avenue jean de bologne 1020 bruxelles'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.896344, $result->getCoordinates()->getLatitude(), '', 0.00001);
        $this->assertEquals(4.3605984, $result->getCoordinates()->getLongitude(), '', 0.00001);
        $this->assertEquals('35', $result->getStreetNumber());
        $this->assertEquals('Avenue Jean de Bologne - Jean de Bolognelaan', $result->getStreetName());
        $this->assertEquals('1020', $result->getPostalCode());
        $this->assertEquals('Ville de Bruxelles - Stad Brussel', $result->getLocality());
        $this->assertEquals('Belgium', $result->getCountry());

        $this->assertEquals(220754533, $result->getOSMId());
        $this->assertEquals('W', $result->getOSMType());
        $this->assertEquals('building', $result->getOSMTag()->key);
        $this->assertEquals('yes', $result->getOSMTag()->value);
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
        $this->assertEquals(52.004657, $result->getCoordinates()->getLatitude(), '', 0.00001);
        $this->assertEquals(10.012148, $result->getCoordinates()->getLongitude(), '', 0.00001);
        $this->assertEquals('31195', $result->getPostalCode());
        $this->assertEquals('Lamspringe', $result->getLocality());
        $this->assertEquals('Germany', $result->getCountry());

        $this->assertEquals(15219847, $result->getOSMId());
        $this->assertEquals('W', $result->getOSMType());
        $this->assertEquals('highway', $result->getOSMTag()->key);
        $this->assertEquals('tertiary', $result->getOSMTag()->value);
    }
}
