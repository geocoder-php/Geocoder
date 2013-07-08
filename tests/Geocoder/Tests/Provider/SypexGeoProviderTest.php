<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\SypexGeoProvider;

/**
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class SypexGeoProviderTest extends TestCase
{
    public function testGeocodeCity()
    {
        $dbFile = __DIR__ . '/fixtures/SxGeoCity.dat';
        if (!is_file($dbFile)) {
            $this->markTestSkipped('No city file found.');
        }

        $provider = new SypexGeoProvider($dbFile);
        $results = $provider->getGeocodedData('46.98.43.114');

        $this->assertCount(1, $results);
        $this->assertEquals(48.464717, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(35.046183, $results[0]['longitude'], '', 0.01);
        $this->assertEquals('Днепропетровск', $results[0]['city']);
        $this->assertEquals('Днепропетровская область', $results[0]['region']);
        $this->assertEquals('UA', $results[0]['countryCode']);
    }

    public function testGeocodeCountry()
    {
        $dbFile = __DIR__ . '/fixtures/SxGeo.dat';
        if (!is_file($dbFile)) {
            $this->markTestSkipped('No country file found.');
        }

        $provider = new SypexGeoProvider($dbFile, null, true);
        $results = $provider->getGeocodedData('46.98.43.114');

        $this->assertCount(1, $results);
        $this->assertEquals('UA', $results[0]['countryCode']);
    }
}
