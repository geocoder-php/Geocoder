<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\BingMaps\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\BingMaps\BingMaps;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class BingMapsTest extends BaseTestCase
{
    protected function getCacheDir(): ?string
    {
        if (isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES']) {
            return __DIR__.'/.cached_responses';
        }

        return null;
    }

    public function testGetName(): void
    {
        $provider = new BingMaps($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('bing_maps', $provider->getName());
    }

    public function testGeocodeWithInvalidData(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new BingMaps($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The BingMaps provider does not support IP addresses, only street addresses.');

        $provider = new BingMaps($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The BingMaps provider does not support IP addresses, only street addresses.');

        $provider = new BingMaps($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeReturnsMultipleResults(): void
    {
        $json = <<<JSON
{"authenticationResultCode":"ValidCredentials","brandLogoUri":"https:\/\/dev.virtualearth.net\/Branding\/logo_powered_by.png","copyright":"Copyright © 2013 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.","resourceSets":[{"estimatedTotal":3,"resources":[{"__type":"Location:https:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.859354042429317,2.3809438666389395,48.86707947757067,2.3966003933610596],"name":"10 Avenue Gambetta, 75020 Paris","point":{"type":"Point","coordinates":[48.863216759999993,2.3887721299999995]},"address":{"addressLine":"10 Avenue Gambetta","adminDistrict":"IdF","adminDistrict2":"Paris","countryRegion":"France","countryRegionIso2":"FR","formattedAddress":"10 Avenue Gambetta, 75020 Paris","locality":"Paris","postalCode":"75020"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.863216759999993,2.3887721299999995],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Ambiguous","Good"]},{"__type":"Location:https:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.809565092429317,2.3172171827738461,48.81729052757067,2.3328581572261538],"name":"10 Avenue Léon Gambetta, 92120 Montrouge","point":{"type":"Point","coordinates":[48.813427809999993,2.32503767]},"address":{"addressLine":"10 Avenue Léon Gambetta","adminDistrict":"IdF","adminDistrict2":"Hauts-de-Seine","countryRegion":"France","countryRegionIso2":"FR","formattedAddress":"10 Avenue Léon Gambetta, 92120 Montrouge","locality":"Montrouge","postalCode":"92120"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.813427809999993,2.32503767],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Ambiguous","Good"]},{"__type":"Location:https:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.806278752429328,2.4278605052896745,48.814004187570681,2.4435004547103261],"name":"10 Avenue Gambetta, 94700 Maisons-Alfort","point":{"type":"Point","coordinates":[48.810141470000005,2.4356804800000003]},"address":{"addressLine":"10 Avenue Gambetta","adminDistrict":"IdF","adminDistrict2":"Val-De-Marne","countryRegion":"France","countryRegionIso2":"FR","formattedAddress":"10 Avenue Gambetta, 94700 Maisons-Alfort","locality":"Maisons-Alfort","postalCode":"94700"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.810141470000005,2.4356804800000003],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Ambiguous","Good"]}]}],"statusCode":200,"statusDescription":"OK","traceId":"fd9b0b8fe1a34ad384923b5d0937bfb2|AMSM001404|02.00.139.700|AMSMSNVM002409, AMSMSNVM001862, AMSMSNVM001322, AMSMSNVM000044"}
JSON;

        $provider = new BingMaps($this->getMockedHttpClient($json), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(3, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.86321675999999, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.3887721299999995, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.859354042429, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(2.3809438666389, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(48.867079477571, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(2.3966003933611, $result->getBounds()->getEast(), 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('IdF', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        $this->assertNull($result->getTimezone());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.81342781, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.32503767, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.809565092429, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(2.3172171827738, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(48.817290527571, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(2.3328581572262, $result->getBounds()->getEast(), 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('10 Avenue Léon Gambetta', $result->getStreetName());
        $this->assertEquals(92120, $result->getPostalCode());
        $this->assertEquals('Montrouge', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Hauts-de-Seine', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('IdF', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.81014147, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.43568048, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.806278752429, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(2.4278605052897, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(48.814004187571, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(2.4435004547103, $result->getBounds()->getEast(), 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(94700, $result->getPostalCode());
        $this->assertEquals('Maisons-Alfort', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Val-De-Marne', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('IdF', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    public function testReverseReturnsSingleResult(): void
    {
        $json = <<<JSON
{"authenticationResultCode":"ValidCredentials","brandLogoUri":"https:\/\/dev.virtualearth.net\/Branding\/logo_powered_by.png","copyright":"Copyright © 2013 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.","resourceSets":[{"estimatedTotal":1,"resources":[{"__type":"Location:https:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.859353771982775,2.3809437325832983,48.867079207124128,2.3966002592208246],"name":"10 Avenue Gambetta, 75020 20e Arrondissement","point":{"type":"Point","coordinates":[48.863216489553452,2.3887719959020615]},"address":{"addressLine":"10 Avenue Gambetta","adminDistrict":"IdF","adminDistrict2":"Paris","countryRegion":"France","countryRegionIso2":"FR","formattedAddress":"10 Avenue Gambetta, 75020 20e Arrondissement","locality":"20e Arrondissement","postalCode":"75020"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.863216489553452,2.3887719959020615],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Good"]}]}],"statusCode":200,"statusDescription":"OK","traceId":"0691dabd257043b381b678fbfaf799dd|AMSM001401|02.00.139.700|AMSMSNVM001951, AMSMSNVM002152"}
JSON;

        $provider = new BingMaps($this->getMockedHttpClient($json), 'api_key');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86321648955345, 2.3887719959020615));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.86321648955345, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(2.3887719959020615, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.859353771983, $result->getBounds()->getSouth(), 0.0001);
        $this->assertEqualsWithDelta(2.3809437325833, $result->getBounds()->getWest(), 0.0001);
        $this->assertEqualsWithDelta(48.867079207124, $result->getBounds()->getNorth(), 0.0001);
        $this->assertEqualsWithDelta(2.3966002592208, $result->getBounds()->getEast(), 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('20e Arrondissement', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('IdF', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressReturnsSingleResults(): void
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getHttpClient($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France')->withLocale('fr-FR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.86321675999999, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.3887721299999995, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.859354042429, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(2.3967310, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(48.867079477571, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(2.3966003933611, $result->getBounds()->getEast(), 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('IdF', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        $this->assertNull($result->getAdminLevels()->get(2)->getCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressReturnsMultipleResults(): void
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getHttpClient($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Castelnuovo, Italie')->withLocale('fr-FR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);
    }

    public function testReverseWithRealCoordinatesReturnsSingleResult(): void
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getHttpClient($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86321648955345, 2.3887719959020615));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.86321648955345, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(2.3887719959020615, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.859353771983, $result->getBounds()->getSouth(), 0.0001);
        $this->assertEqualsWithDelta(2.3809437325833, $result->getBounds()->getWest(), 0.0001);
        $this->assertEqualsWithDelta(48.867079207124, $result->getBounds()->getNorth(), 0.0001);
        $this->assertEqualsWithDelta(2.3966002592208, $result->getBounds()->getEast(), 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('3 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The BingMaps provider does not support IP addresses, only street addresses.');

        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getHttpClient($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY']);
        $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14'));
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The BingMaps provider does not support IP addresses, only street addresses.');

        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getHttpClient($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY']);
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }
}
