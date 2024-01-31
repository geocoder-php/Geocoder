<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Faker\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Model\Address;
use Geocoder\Provider\Faker\Faker;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class FakerTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new Faker();

        $this->assertEquals('faker', $provider->getName());
    }

    public function testGeocode(): void
    {
        $provider = new Faker();
        $result = $provider->geocodeQuery(GeocodeQuery::create('Dummy address'));

        $this->assertContainsOnlyInstancesOf(Address::class, $result);
        $this->assertCount(5, $result);
    }

    public function testReverse(): void
    {
        $provider = new Faker();
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86, 2.35));

        $this->assertContainsOnlyInstancesOf(Address::class, $result);
        $this->assertCount(5, $result);
    }

    public function testQueryWithLimit(): void
    {
        $provider = new Faker();
        $result = $provider->geocodeQuery(GeocodeQuery::create('Dummy address')->withLimit(10));

        $this->assertContainsOnlyInstancesOf(Address::class, $result);
        $this->assertCount(10, $result);
    }
}
