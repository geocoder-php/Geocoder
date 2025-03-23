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
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Photon\Model\PhotonAddress;
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

        /** @var PhotonAddress $result */
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

        /** @var PhotonAddress $result */
        $result = $results->first();

        $this->assertEquals('The Sherlock Holmes Museum and shop', $result->getName());
    }

    public function testGeocodeQueryWithOsmTagFilter(): void
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $query = GeocodeQuery::create('Paris')
            ->withData('osm_tag', 'tourism:museum')
            ->withLimit(5);
        $results = $provider->geocodeQuery($query);

        $this->assertCount(5, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf(PhotonAddress::class, $result);
            $this->assertEquals('tourism', $result->getOSMTag()->key);
            $this->assertEquals('museum', $result->getOSMTag()->value);
        }
    }

    public function testGeocodeQueryWithMultipleOsmTagFilter(): void
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $query = GeocodeQuery::create('Paris')
            ->withData('osm_tag', ['tourism:museum', 'tourism:gallery'])
            ->withLimit(10);
        $results = $provider->geocodeQuery($query);

        $this->assertCount(10, $results);
        $countMuseums = $countGalleries = 0;
        foreach ($results as $result) {
            $this->assertInstanceOf(PhotonAddress::class, $result);
            $this->assertEquals('tourism', $result->getOSMTag()->key);
            $this->assertContains($result->getOSMTag()->value, ['museum', 'gallery']);
            if ('museum' === $result->getOSMTag()->value) {
                ++$countMuseums;
            } elseif ('gallery' === $result->getOSMTag()->value) {
                ++$countGalleries;
            }
        }
        $this->assertGreaterThan(0, $countMuseums);
        $this->assertGreaterThan(0, $countGalleries);
    }

    public function testGeocodeQueryWithLatLon(): void
    {
        $provider = Photon::withKomootServer($this->getHttpClient());

        $query = GeocodeQuery::create('Paris')->withLimit(1);
        $results = $provider->geocodeQuery($query);
        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);
        $this->assertEquals('France', $results->first()->getCountry());

        $query = $query
            ->withData('lat', 33.661426)
            ->withData('lon', -95.556321);
        $results = $provider->geocodeQuery($query);
        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);
        $this->assertEquals('United States', $results->first()->getCountry());
    }

    public function testReverseQuery(): void
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $reverseQuery = ReverseQuery::fromCoordinates(52, 10)->withLimit(1);
        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var PhotonAddress $result */
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

    public function testReverseQueryWithOsmTagFilter(): void
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $reverseQuery = ReverseQuery::fromCoordinates(52.51644, 13.38890)
            ->withData('osm_tag', 'amenity:pharmacy')
            ->withLimit(3);
        $results = $provider->reverseQuery($reverseQuery);

        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf(PhotonAddress::class, $result);
            $this->assertEquals('amenity', $result->getOSMTag()->key);
            $this->assertEquals('pharmacy', $result->getOSMTag()->value);
        }
    }

    public function testReverseQueryWithLayerCityAndRadiusFilter(): void
    {
        $provider = Photon::withKomootServer($this->getHttpClient());
        $reverseQuery = ReverseQuery::fromCoordinates(52.51644, 13.38890)
            ->withData('layer', 'city')
            ->withData('radius', 20)
            ->withLimit(1);
        $result = $provider->reverseQuery($reverseQuery)->first();

        $this->assertInstanceOf(PhotonAddress::class, $result);
        $this->assertEquals('city', $result->getType());
        $this->assertEquals('Berlin', $result->getLocality());
    }

    public function testGeocodeQueryWithBbox(): void
    {
        // Germany
        $bounds = new \Geocoder\Model\Bounds(
            south: 47.2701,
            west: 5.8663,
            north: 55.992,
            east: 15.0419
        );

        $provider = Photon::withKomootServer($this->getHttpClient());
        $query = GeocodeQuery::create('Paris')
            ->withLimit(5);
        $results = $provider->geocodeQuery($query);

        $this->assertCount(5, $results);
        $this->assertEquals('France', $results->first()->getCountry());
        $this->assertEquals('Paris', $results->first()->getLocality());

        $query = GeocodeQuery::create('Paris')
            ->withBounds($bounds)
            ->withLimit(5);
        $results = $provider->geocodeQuery($query);

        $this->assertCount(2, $results);
        $this->assertEquals('Deutschland', $results->first()->getCountry());
        $this->assertEquals('Wörrstadt', $results->first()->getLocality());
    }
}
