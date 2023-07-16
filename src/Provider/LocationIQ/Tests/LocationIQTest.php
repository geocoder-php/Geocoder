<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\LocationIQ\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\LocationIQ\LocationIQ;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class LocationIQTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGeocodeWithAddressGetsEmptyContent(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new LocationIQ($this->getMockedHttpClient('<foo></foo>'), $_SERVER['LOCATIONIQ_API_KEY']);
        $provider->geocodeQuery(GeocodeQuery::create('Läntinen Pitkäkatu 35, Turku'));
    }

    public function testGeocodeWithAddressGetsEmptyXML(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $emptyXML = <<<'XML'
<?xml version="1.0" encoding="utf-8"?><searchresults_empty></searchresults_empty>
XML;

        $provider = new LocationIQ($this->getMockedHttpClient($emptyXML), $_SERVER['LOCATIONIQ_API_KEY']);
        $provider->geocodeQuery(GeocodeQuery::create('Läntinen Pitkäkatu 35, Turku'));
    }

    public function testGeocodeWithInvalidRegion(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidArgument::class);

        $emptyXML = <<<'XML'
<?xml version="1.0" encoding="utf-8"?><searchresults_empty></searchresults_empty>
XML;

        $provider = new LocationIQ($this->getMockedHttpClient($emptyXML), $_SERVER['LOCATIONIQ_API_KEY'], 'invalid');
    }

    public function testReverseWithCoordinatesGetsError(): void
    {
        $errorXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<reversegeocode querystring='format=xml&amp;lat=-80.000000&amp;lon=-170.000000&amp;addressdetails=1'>
    <error>Unable to geocode</error>
</reversegeocode>
XML;

        $provider = new LocationIQ($this->getMockedHttpClient($errorXml), $_SERVER['LOCATIONIQ_API_KEY']);

        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(-80.000000, -170.000000));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGetNodeStreetName(): void
    {
        $provider = new LocationIQ($this->getHttpClient($_SERVER['LOCATIONIQ_API_KEY']), $_SERVER['LOCATIONIQ_API_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86, 2.35));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Rue Quincampoix', $result->getStreetName());
    }
}
