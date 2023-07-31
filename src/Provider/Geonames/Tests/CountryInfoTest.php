<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Geonames\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\Geonames\Geonames;
use Geocoder\Provider\Geonames\Model\CountryInfo;

class CountryInfoTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testCountryInfoWithOneCountry(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->getCountryInfo('IN');

        $this->assertIsArray($results);
        $this->assertEquals(1, count($results));

        /* @var CountryInfo $result */
        $result = current($results);

        $this->assertInstanceOf('Geocoder\Provider\Geonames\Model\CountryInfo', $result);
        $this->assertInstanceOf('Geocoder\Model\Bounds', $result->getBounds());
        $this->assertEquals('AS', $result->getContinent());
        $this->assertEquals('New Delhi', $result->getCapital());
        $this->assertIsArray($result->getLanguages());
        $this->assertEquals(1269750, $result->getGeonameId());
        $this->assertEquals('IND', $result->getIsoAlpha3());
        $this->assertEquals('IN', $result->getFipsCode());
        $this->assertEquals(1173108018, $result->getPopulation());
        $this->assertEquals(356, $result->getIsoNumeric());
        $this->assertEquals(3287590.0, $result->getAreaInSqKm());
        $this->assertEquals('IN', $result->getCountryCode());
        $this->assertEquals('India', $result->getCountryName());
        $this->assertEquals('Asia', $result->getContinentName());
        $this->assertEquals('INR', $result->getCurrencyCode());
    }

    public function testCountryInfoWithMultipleCountries(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->getCountryInfo('IN,US');

        $this->assertEquals(2, count($results));
    }

    public function testCountryInfoWithInvalidCountry(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->getCountryInfo('AA');

        $this->assertEquals(0, count($results));
    }

    public function testCountryInfoWithLocale(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->getCountryInfo('IN', 'it');

        /* @var CountryInfo $result */
        $result = current($results);

        $this->assertEquals('Nuova Delhi', $result->getCapital());
    }

    public function testCountryInfoWithNoCountry(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->getCountryInfo();

        $this->assertEquals(250, count($results));
    }
}
