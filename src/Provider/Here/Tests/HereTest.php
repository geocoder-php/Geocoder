<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */

namespace Geocoder\Provider\Here\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Here\Here;
use Http\Client\Curl\Client as HttplugClient;

class HereTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    /**
     * Get a real HTTP client. If a cache dir is set to a path it will use cached responses.
     *
     * @return HttpClient
     */
    protected function getHttpClient($apiKey = null, $appCode = null)
    {
        if (null !== $cacheDir = $this->getCacheDir()) {
            return new HereCachedResponseClient(new HttplugClient(), $cacheDir, $apiKey, $appCode);
        } else {
            return new HttplugClient();
        }
    }

    // testGeocodeQuery()

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['HERE_APP_ID']) || !isset($_SERVER['HERE_APP_CODE'])) {
            $this->markTestSkipped('You need to configure the HERE_APP_ID and HERE_APP_CODE value in phpunit.xml');
        }

        $provider = new Here($this->getHttpClient($_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']), $_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France')->withLocale('fr-FR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8653, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.39844, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.8664242, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.3967311, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.8641758, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.4001489, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals(10, $result->getStreetNumber());

        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['HERE_APP_ID']) || !isset($_SERVER['HERE_APP_CODE'])) {
            $this->markTestSkipped('You need to configure the HERE_APP_ID and HERE_APP_CODE value in phpunit.xml');
        }

        $provider = new Here($this->getHttpClient($_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']), $_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8632156, 2.3887722));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8632156, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(2.3887722, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.86323, $result->getBounds()->getSouth(), '', 0.0001);
        $this->assertEquals(2.38847, $result->getBounds()->getWest(), '', 0.0001);
        $this->assertEquals(48.86323, $result->getBounds()->getNorth(), '', 0.0001);
        $this->assertEquals(2.38883, $result->getBounds()->getEast(), '', 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
    }

    public function testGetName()
    {
        $provider = new Here($this->getMockedHttpClient(), 'appId', 'appCode');
        $this->assertEquals('Here', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithInvalidData()
    {
        $provider = new Here($this->getMockedHttpClient(), 'appId', 'appCode');
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Here provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeIpv4()
    {
        if (!isset($_SERVER['HERE_APP_ID']) || !isset($_SERVER['HERE_APP_CODE'])) {
            $this->markTestSkipped('You need to configure the HERE_APP_ID and HERE_APP_CODE value in phpunit.xml');
        }

        $provider = new Here($this->getHttpClient(), $_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']);
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Here provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new Here($this->getMockedHttpClient(), 'appId', 'appCode');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid or missing api key.
     */
    public function testGeocodeInvalidApiKey()
    {
        $provider = new Here(
            $this->getMockedHttpClient(
                '{
					"type": {
						"subtype": "InvalidCredentials"
					}
                }'
            ),
            'appId',
            'appCode'
        );
        $provider->geocodeQuery(GeocodeQuery::create('New York'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Here provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['HERE_APP_ID']) || !isset($_SERVER['HERE_APP_CODE'])) {
            $this->markTestSkipped('You need to configure the HERE_APP_ID and HERE_APP_CODE value in phpunit.xml');
        }

        $provider = new Here($this->getHttpClient(), $_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']);
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }
}
