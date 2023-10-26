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
use Geocoder\Provider\Pelias\Pelias;
use Geocoder\Query\GeocodeQuery;

class AdminLevelTest extends BaseTestCase
{

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testAdminLevelsOrder()
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
        $this->assertSame(1, $result->count());
        $address = $result->get(0);

        $expectedOrder = [
            'COUNTRY',
            'MACROREGION',
            'REGION',
            'COUNTY',
            'LOCALITY',
        ];

        foreach ($address->getAdminLevels()->all() as $key => $adminLevel) {
            $this->assertSame($expectedOrder[$key-1], $adminLevel->getName());
            $this->assertSame($key, $adminLevel->getLevel(), 'Invalid admin level number for level '.$adminLevel->getName());
        }
    }
}