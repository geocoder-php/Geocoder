<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\BingMaps;

class BingMapsTest extends TestCase
{
    public function testGetName()
    {
        $provider = new BingMaps($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('bing_maps', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGeocodeWithNullApiKey()
    {
        $provider = new BingMaps($this->getMockAdapter($this->never()), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/?maxResults=5&q=foobar&key=api_key
     */
    public function testGeocodeWithInvalidData()
    {
        $provider = new BingMaps($this->getMockAdapter(), 'api_key');
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/?maxResults=5&q=&key=api_key
     */
    public function testGeocodeWithNull()
    {
        $provider = new BingMaps($this->getMockAdapter(), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/?maxResults=5&q=&key=api_key
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new BingMaps($this->getMockAdapter(), 'api_key');
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The BingMaps does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new BingMaps($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The BingMaps does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new BingMaps($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/?maxResults=5&q=10+avenue+Gambetta%2C+Paris%2C+France&key=api_key
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new BingMaps($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeReturnsMultipleResults()
    {
        $json = <<<JSON
{"authenticationResultCode":"ValidCredentials","brandLogoUri":"http:\/\/dev.virtualearth.net\/Branding\/logo_powered_by.png","copyright":"Copyright © 2013 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.","resourceSets":[{"estimatedTotal":3,"resources":[{"__type":"Location:http:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.859354042429317,2.3809438666389395,48.86707947757067,2.3966003933610596],"name":"10 Avenue Gambetta, 75020 Paris","point":{"type":"Point","coordinates":[48.863216759999993,2.3887721299999995]},"address":{"addressLine":"10 Avenue Gambetta","adminDistrict":"IdF","adminDistrict2":"Paris","countryRegion":"France","formattedAddress":"10 Avenue Gambetta, 75020 Paris","locality":"Paris","postalCode":"75020"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.863216759999993,2.3887721299999995],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Ambiguous","Good"]},{"__type":"Location:http:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.809565092429317,2.3172171827738461,48.81729052757067,2.3328581572261538],"name":"10 Avenue Léon Gambetta, 92120 Montrouge","point":{"type":"Point","coordinates":[48.813427809999993,2.32503767]},"address":{"addressLine":"10 Avenue Léon Gambetta","adminDistrict":"IdF","adminDistrict2":"Hauts-de-Seine","countryRegion":"France","formattedAddress":"10 Avenue Léon Gambetta, 92120 Montrouge","locality":"Montrouge","postalCode":"92120"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.813427809999993,2.32503767],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Ambiguous","Good"]},{"__type":"Location:http:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.806278752429328,2.4278605052896745,48.814004187570681,2.4435004547103261],"name":"10 Avenue Gambetta, 94700 Maisons-Alfort","point":{"type":"Point","coordinates":[48.810141470000005,2.4356804800000003]},"address":{"addressLine":"10 Avenue Gambetta","adminDistrict":"IdF","adminDistrict2":"Val-De-Marne","countryRegion":"France","formattedAddress":"10 Avenue Gambetta, 94700 Maisons-Alfort","locality":"Maisons-Alfort","postalCode":"94700"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.810141470000005,2.4356804800000003],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Ambiguous","Good"]}]}],"statusCode":200,"statusDescription":"OK","traceId":"fd9b0b8fe1a34ad384923b5d0937bfb2|AMSM001404|02.00.139.700|AMSMSNVM002409, AMSMSNVM001862, AMSMSNVM001322, AMSMSNVM000044"}
JSON;

        $provider = new BingMaps($this->getMockAdapterReturns($json), 'api_key', 'fr_FR');
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(3, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.86321675999999, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(2.3887721299999995, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(48.859354042429, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.3809438666389, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.867079477571, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.3966003933611, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $results[0]['streetName']);
        $this->assertEquals(75020, $results[0]['postalCode']);
        $this->assertEquals('Paris', $results[0]['locality']);
        $this->assertEquals('Paris', $results[0]['county']);
        $this->assertEquals('IdF', $results[0]['region']);
        $this->assertEquals('France', $results[0]['country']);

        $this->assertNull($results[0]['countryCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(48.81342781, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(2.32503767, $results[1]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertEquals(48.809565092429, $results[1]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.3172171827738, $results[1]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.817290527571, $results[1]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.3328581572262, $results[1]['bounds']['east'], '', 0.01);
        $this->assertNull($results[1]['streetNumber']);
        $this->assertEquals('10 Avenue Léon Gambetta', $results[1]['streetName']);
        $this->assertEquals(92120, $results[1]['postalCode']);
        $this->assertEquals('Montrouge', $results[1]['locality']);
        $this->assertEquals('Hauts-de-Seine', $results[1]['county']);
        $this->assertEquals('IdF', $results[1]['region']);
        $this->assertEquals('France', $results[1]['country']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(48.81014147, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(2.43568048, $results[2]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[2]['bounds']);
        $this->assertArrayHasKey('west', $results[2]['bounds']);
        $this->assertArrayHasKey('north', $results[2]['bounds']);
        $this->assertArrayHasKey('east', $results[2]['bounds']);
        $this->assertEquals(48.806278752429, $results[2]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.4278605052897, $results[2]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.814004187571, $results[2]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.4435004547103, $results[2]['bounds']['east'], '', 0.01);
        $this->assertNull($results[2]['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $results[2]['streetName']);
        $this->assertEquals(94700, $results[2]['postalCode']);
        $this->assertEquals('Maisons-Alfort', $results[2]['locality']);
        $this->assertEquals('Val-De-Marne', $results[2]['county']);
        $this->assertEquals('IdF', $results[2]['region']);
        $this->assertEquals('France', $results[2]['country']);
    }

    public function testReverseReturnsSingleResult()
    {
        $json = <<<JSON
{"authenticationResultCode":"ValidCredentials","brandLogoUri":"http:\/\/dev.virtualearth.net\/Branding\/logo_powered_by.png","copyright":"Copyright © 2013 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.","resourceSets":[{"estimatedTotal":1,"resources":[{"__type":"Location:http:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.859353771982775,2.3809437325832983,48.867079207124128,2.3966002592208246],"name":"10 Avenue Gambetta, 75020 20e Arrondissement","point":{"type":"Point","coordinates":[48.863216489553452,2.3887719959020615]},"address":{"addressLine":"10 Avenue Gambetta","adminDistrict":"IdF","adminDistrict2":"Paris","countryRegion":"France","formattedAddress":"10 Avenue Gambetta, 75020 20e Arrondissement","locality":"20e Arrondissement","postalCode":"75020"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.863216489553452,2.3887719959020615],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Good"]}]}],"statusCode":200,"statusDescription":"OK","traceId":"0691dabd257043b381b678fbfaf799dd|AMSM001401|02.00.139.700|AMSMSNVM001951, AMSMSNVM002152"}
JSON;

        $provider = new BingMaps($this->getMockAdapterReturns($json), 'api_key');
        $results  = $provider->reverse(48.86321648955345, 2.3887719959020615);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.86321648955345, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3887719959020615, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.859353771983, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.3809437325833, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.867079207124, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.3966002592208, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['postalCode']);
        // $this->assertEquals('Paris', $result['locality']);
        $this->assertEquals('20e Arrondissement', $result['locality']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('IdF', $result['region']);
        $this->assertEquals('France', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGeocodeWithRealAddressReturnsMultipleResults()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getAdapter(), $_SERVER['BINGMAPS_API_KEY'], 'fr-FR');
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(3, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.86321675999999, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(2.3887721299999995, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(48.859354042429, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.3809438666389, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.867079477571, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.3966003933611, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $results[0]['streetName']);
        $this->assertEquals(75020, $results[0]['postalCode']);
        $this->assertEquals('Paris', $results[0]['locality']);
        $this->assertEquals('Paris', $results[0]['county']);
        $this->assertEquals('IdF', $results[0]['region']);
        $this->assertEquals('France', $results[0]['country']);

        $this->assertNull($results[0]['countryCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(48.81342781, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(2.32503767, $results[1]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertEquals(48.809565092429, $results[1]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.3172171827738, $results[1]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.817290527571, $results[1]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.3328581572262, $results[1]['bounds']['east'], '', 0.01);
        $this->assertNull($results[1]['streetNumber']);
        $this->assertEquals('10 Avenue Léon Gambetta', $results[1]['streetName']);
        $this->assertEquals(92120, $results[1]['postalCode']);
        $this->assertEquals('Montrouge', $results[1]['locality']);
        $this->assertEquals('Hauts-de-Seine', $results[1]['county']);
        $this->assertEquals('IdF', $results[1]['region']);
        $this->assertEquals('France', $results[1]['country']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(48.81014147, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(2.43568048, $results[2]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[2]['bounds']);
        $this->assertArrayHasKey('west', $results[2]['bounds']);
        $this->assertArrayHasKey('north', $results[2]['bounds']);
        $this->assertArrayHasKey('east', $results[2]['bounds']);
        $this->assertEquals(48.806278752429, $results[2]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.4278605052897, $results[2]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.814004187571, $results[2]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.4435004547103, $results[2]['bounds']['east'], '', 0.01);
        $this->assertNull($results[2]['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $results[2]['streetName']);
        $this->assertEquals(94700, $results[2]['postalCode']);
        $this->assertEquals('Maisons-Alfort', $results[2]['locality']);
        $this->assertEquals('Val-De-Marne', $results[2]['county']);
        $this->assertEquals('IdF', $results[2]['region']);
        $this->assertEquals('France', $results[2]['country']);
    }

    /**
     * @expectedException Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/1.000000,2.000000?key=api_key
     */
    public function testReverse()
    {
        $provider = new BingMaps($this->getMockAdapter(), 'api_key');
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/48.863216,2.388772?key=api_key
     */
    public function testReverseWithCoordinatesContentReturnNull()
    {
        $provider = new BingMaps($this->getMockAdapterReturns(null), 'api_key');
        $provider->reverse(48.86321648955345, 2.3887719959020615);
    }

    public function testReverseWithRealCoordinatesReturnsSingleResult()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $results  = $provider->reverse(48.86321648955345, 2.3887719959020615);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.86321648955345, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3887719959020615, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.859353771983, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.3809437325833, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.867079207124, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.3966002592208, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['postalCode']);
        // $this->assertEquals('Paris', $result['locality']);
        $this->assertEquals('20e Arrondissement', $result['locality']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('IdF', $result['region']);
        $this->assertEquals('France', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The BingMaps does not support IP addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $provider->geocode('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The BingMaps does not support IP addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $provider->geocode('::ffff:88.188.221.14');
    }
}
