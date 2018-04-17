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

class HereTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        if (isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES']) {
            return __DIR__.'/.cached_responses';
        }

        return null;
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
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Here($this->getMockedHttpClient(), 'appId', 'appCode');
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

    public function testReverseReturnsSingleResult()
    {
        $json = <<<JSON
{"Response":{"MetaInfo":{"Timestamp":"2018-04-17T13:45:54.394+0000"},"View":[{"_type":"SearchResultsViewType","ViewId":0,"Result":[{"Relevance":1.0,"Distance":0.1,"MatchLevel":"street","MatchQuality":{"Country":1.0,"State":1.0,"County":1.0,"City":1.0,"District":1.0,"Street":[1.0],"PostalCode":1.0},"Location":{"LocationId":"NT_-nSC1VIpTK6RxGk-RZa.1D_l_1212860159_L","LocationType":"address","DisplayPosition":{"Latitude":48.8632156,"Longitude":2.3887722},"NavigationPosition":[{"Latitude":48.8632156,"Longitude":2.3887722}],"MapView":{"TopLeft":{"Latitude":48.86323,"Longitude":2.38847},"BottomRight":{"Latitude":48.86314,"Longitude":2.38883}},"Address":{"Label":"Avenue Gambetta, 75020 Paris, France","Country":"FRA","State":"Île-de-France","County":"Paris","City":"Paris","District":"20e Arrondissement","Street":"Avenue Gambetta","PostalCode":"75020","AdditionalData":[{"value":"France","key":"CountryName"},{"value":"Île-de-France","key":"StateName"},{"value":"Paris","key":"CountyName"}]},"MapReference":{"ReferenceId":"1212860159","MapId":"UWAM18105","MapVersion":"Q1/2018","MapReleaseDate":"2018-04-10","Spot":0.84,"SideOfStreet":"neither","CountryId":"20000001","StateId":"20002126","CountyId":"20002127","CityId":"20002128","DistrictId":"20002149"}}}]}]}}
JSON;

        $provider = new Here($this->getMockedHttpClient($json), 'appId', 'appCode');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86321648955345, 2.3887719959020615));

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
        $this->assertEquals(48.86314, $result->getBounds()->getNorth(), '', 0.0001);
        $this->assertEquals(2.38883, $result->getBounds()->getEast(), '', 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
        
    }

    public function testGeocodeWithRealAddressReturnsSingleResults()
    {
        if (!isset($_SERVER['HERE_APP_ID']) || !isset($_SERVER['HERE_APP_CODE'])) {
            $this->markTestSkipped('You need to configure the HERE_APP_ID and HERE_APP_CODE value in phpunit.xml');
        }

        $provider = new Here($this->getHttpClient(), $_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']);
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


    public function testReverseWithRealCoordinatesReturnsSingleResult()
    {
                if (!isset($_SERVER['HERE_APP_ID']) || !isset($_SERVER['HERE_APP_CODE'])) {
            $this->markTestSkipped('You need to configure the HERE_APP_ID and HERE_APP_CODE value in phpunit.xml');
        }

        $provider = new Here($this->getHttpClient(), $_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86321648955345, 2.3887719959020615));

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
        $this->assertEquals(48.86339, $result->getBounds()->getNorth(), '', 0.0001);
        $this->assertEquals(2.38883, $result->getBounds()->getEast(), '', 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());

    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Here provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        if (!isset($_SERVER['HERE_APP_ID']) || !isset($_SERVER['HERE_APP_CODE'])) {
            $this->markTestSkipped('You need to configure the HERE_APP_ID and HERE_APP_CODE value in phpunit.xml');
        }

        $provider = new Here($this->getHttpClient(), $_SERVER['HERE_APP_ID'], $_SERVER['HERE_APP_CODE']);
        $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14'));
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
