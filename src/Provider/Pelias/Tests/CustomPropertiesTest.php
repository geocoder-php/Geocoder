<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Provider\Pelias\Model\PeliasAddress;
use Geocoder\Provider\Pelias\Pelias;
use Geocoder\Query\GeocodeQuery;

class CustomPropertiesTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testPeliasAddressReturned()
    {
        $response = '
        {
            "type": "FeatureCollection",
            "features": [
                {
                    "type": "Feature",
                    "geometry": {
                        "coordinates": [
                            1.000,
                            1.000
                        ]
                    },
                    "properties": {
                        "source": "openaddresses",
                        "layer": "address",
                        "confidence": 1,
                        "match_type": "exact",
                        "accuracy": "point",

                        "country": "COUNTRY",
                        "macroregion": "MACROREGION",
                        "region": "REGION",
                        "county": "COUNTY",
                        "locality": "LOCALITY",
                        "neighbourhood": "NEIGHBORHOOD"
                    }
                }
            ]
        }
        ';
        $provider = new Pelias($this->getMockedHttpClient($response), 'http://localhost/');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(1, $result->count());
        $address = $result->get(0);

        $this->assertInstanceOf(PeliasAddress::class, $address);

        $this->assertEquals('openaddresses', $address->getSource());
        $this->assertEquals('address', $address->getLayer());
        $this->assertEquals(1, $address->getConfidence());
        $this->assertEquals('exact', $address->getMatchType());
        $this->assertEquals('point', $address->getAccuracy());
    }

    public function testWithSource()
    {
        $address = new PeliasAddress('Pelias', new AdminLevelCollection());
        $newAddress = $address->withSource('openaddresses');
        $this->assertEquals('openaddresses', $newAddress->getSource());
        $this->assertNull($address->getSource());
    }

    public function testWithLayer()
    {
        $address = new PeliasAddress('Pelias', new AdminLevelCollection());
        $newAddress = $address->withLayer('address');
        $this->assertEquals('address', $newAddress->getLayer());
        $this->assertNull($address->getLayer());
    }

    public function testWithConfidence()
    {
        $address = new PeliasAddress('Pelias', new AdminLevelCollection());
        $newAddress = $address->withConfidence(1);
        $this->assertEquals(1, $newAddress->getConfidence());
        $this->assertNull($address->getConfidence());
    }

    public function testWithMatchType()
    {
        $address = new PeliasAddress('Pelias', new AdminLevelCollection());
        $newAddress = $address->withMatchType('exact');
        $this->assertEquals('exact', $newAddress->getMatchType());
        $this->assertNull($address->getMatchType());
    }

    public function testWithAccuracy()
    {
        $address = new PeliasAddress('Pelias', new AdminLevelCollection());
        $newAddress = $address->withAccuracy('point');
        $this->assertEquals('point', $newAddress->getAccuracy());
        $this->assertNull($address->getAccuracy());
    }
    
}
