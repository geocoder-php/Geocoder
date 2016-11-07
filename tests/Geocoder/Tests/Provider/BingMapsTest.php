<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Location;
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
     * @expectedExceptionMessage Could not execute query "http://dev.virtualearth.net/REST/v1/Locations/?maxResults=5&q=foobar&key=api_key&incl=ciso2".
     */
    public function testGeocodeWithInvalidData()
    {
        $provider = new BingMaps($this->getMockAdapter(), 'api_key');
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://dev.virtualearth.net/REST/v1/Locations/?maxResults=5&q=&key=api_key&incl=ciso2".
     */
    public function testGeocodeWithNull()
    {
        $provider = new BingMaps($this->getMockAdapter(), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://dev.virtualearth.net/REST/v1/Locations/?maxResults=5&q=&key=api_key&incl=ciso2".
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new BingMaps($this->getMockAdapter(), 'api_key');
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The BingMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new BingMaps($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The BingMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new BingMaps($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://dev.virtualearth.net/REST/v1/Locations/?maxResults=5&q=10+avenue+Gambetta%2C+Paris%2C+France&key=api_key&incl=ciso2".
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new BingMaps($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeReturnsMultipleResults()
    {
        $json = <<<JSON
{"authenticationResultCode":"ValidCredentials","brandLogoUri":"http:\/\/dev.virtualearth.net\/Branding\/logo_powered_by.png","copyright":"Copyright © 2013 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.","resourceSets":[{"estimatedTotal":3,"resources":[{"__type":"Location:http:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.859354042429317,2.3809438666389395,48.86707947757067,2.3966003933610596],"name":"10 Avenue Gambetta, 75020 Paris","point":{"type":"Point","coordinates":[48.863216759999993,2.3887721299999995]},"address":{"addressLine":"10 Avenue Gambetta","adminDistrict":"IdF","adminDistrict2":"Paris","countryRegion":"France","countryRegionIso2":"FR","formattedAddress":"10 Avenue Gambetta, 75020 Paris","locality":"Paris","postalCode":"75020"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.863216759999993,2.3887721299999995],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Ambiguous","Good"]},{"__type":"Location:http:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.809565092429317,2.3172171827738461,48.81729052757067,2.3328581572261538],"name":"10 Avenue Léon Gambetta, 92120 Montrouge","point":{"type":"Point","coordinates":[48.813427809999993,2.32503767]},"address":{"addressLine":"10 Avenue Léon Gambetta","adminDistrict":"IdF","adminDistrict2":"Hauts-de-Seine","countryRegion":"France","countryRegionIso2":"FR","formattedAddress":"10 Avenue Léon Gambetta, 92120 Montrouge","locality":"Montrouge","postalCode":"92120"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.813427809999993,2.32503767],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Ambiguous","Good"]},{"__type":"Location:http:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.806278752429328,2.4278605052896745,48.814004187570681,2.4435004547103261],"name":"10 Avenue Gambetta, 94700 Maisons-Alfort","point":{"type":"Point","coordinates":[48.810141470000005,2.4356804800000003]},"address":{"addressLine":"10 Avenue Gambetta","adminDistrict":"IdF","adminDistrict2":"Val-De-Marne","countryRegion":"France","countryRegionIso2":"FR","formattedAddress":"10 Avenue Gambetta, 94700 Maisons-Alfort","locality":"Maisons-Alfort","postalCode":"94700"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.810141470000005,2.4356804800000003],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Ambiguous","Good"]}]}],"statusCode":200,"statusDescription":"OK","traceId":"fd9b0b8fe1a34ad384923b5d0937bfb2|AMSM001404|02.00.139.700|AMSMSNVM002409, AMSMSNVM001862, AMSMSNVM001322, AMSMSNVM000044"}
JSON;

        $provider = new BingMaps($this->getMockAdapterReturns($json), 'api_key', 'fr_FR');
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(3, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.86321675999999, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.3887721299999995, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.859354042429, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.3809438666389, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.867079477571, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.3966003933611, $result->getBounds()->getEast(), '', 0.01);
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
        $this->assertEquals(48.81342781, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.32503767, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.809565092429, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.3172171827738, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.817290527571, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.3328581572262,$result->getBounds()->getEast(), '', 0.01);
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
        $this->assertEquals(48.81014147, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.43568048, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.806278752429, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.4278605052897, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.814004187571, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.4435004547103, $result->getBounds()->getEast(), '', 0.01);
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

    public function testReverseReturnsSingleResult()
    {
        $json = <<<JSON
{"authenticationResultCode":"ValidCredentials","brandLogoUri":"http:\/\/dev.virtualearth.net\/Branding\/logo_powered_by.png","copyright":"Copyright © 2013 Microsoft and its suppliers. All rights reserved. This API cannot be accessed and the content and any results may not be used, reproduced or transmitted in any manner without express written permission from Microsoft Corporation.","resourceSets":[{"estimatedTotal":1,"resources":[{"__type":"Location:http:\/\/schemas.microsoft.com\/search\/local\/ws\/rest\/v1","bbox":[48.859353771982775,2.3809437325832983,48.867079207124128,2.3966002592208246],"name":"10 Avenue Gambetta, 75020 20e Arrondissement","point":{"type":"Point","coordinates":[48.863216489553452,2.3887719959020615]},"address":{"addressLine":"10 Avenue Gambetta","adminDistrict":"IdF","adminDistrict2":"Paris","countryRegion":"France","countryRegionIso2":"FR","formattedAddress":"10 Avenue Gambetta, 75020 20e Arrondissement","locality":"20e Arrondissement","postalCode":"75020"},"confidence":"Medium","entityType":"Address","geocodePoints":[{"type":"Point","coordinates":[48.863216489553452,2.3887719959020615],"calculationMethod":"Interpolation","usageTypes":["Display","Route"]}],"matchCodes":["Good"]}]}],"statusCode":200,"statusDescription":"OK","traceId":"0691dabd257043b381b678fbfaf799dd|AMSM001401|02.00.139.700|AMSMSNVM001951, AMSMSNVM002152"}
JSON;

        $provider = new BingMaps($this->getMockAdapterReturns($json), 'api_key');
        $results  = $provider->reverse(48.86321648955345, 2.3887719959020615);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.86321648955345, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(2.3887719959020615, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.859353771983, $result->getBounds()->getSouth(), '', 0.0001);
        $this->assertEquals(2.3809437325833, $result->getBounds()->getWest(), '', 0.0001);
        $this->assertEquals(48.867079207124, $result->getBounds()->getNorth(), '', 0.0001);
        $this->assertEquals(2.3966002592208, $result->getBounds()->getEast(), '', 0.0001);
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

    public function testGeocodeWithRealAddressReturnsSingleResults()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getAdapter($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY'], 'fr-FR');
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.86321675999999, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.3887721299999995, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.859354042429, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.3809438666389, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.867079477571, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.3966003933611, $result->getBounds()->getEast(), '', 0.01);
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

    public function testGeocodeWithRealAddressReturnsMultipleResults()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getAdapter($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY'], 'fr-FR');
        $results  = $provider->geocode('Castelnuovo, Italie');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->get(0);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(44.786701202393, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(8.2841901779175, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(44.775325775146, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(8.2711343765259, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(44.795879364014, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(8.296314239502, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEmpty($result->getStreetName());
        $this->assertEmpty($result->getPostalCode());
        $this->assertEquals('Castelnuovo Calcea', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('AT', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Piem.', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Italie', $result->getCountry()->getName());
        $this->assertEquals('IT', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(46.05179977417, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(11.497699737549, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(46.029235839844, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(11.473880767822, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(46.07377243042, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(11.51912689209, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEmpty($result->getStreetName());
        $this->assertEmpty($result->getPostalCode());
        $this->assertEquals('Castelnuovo', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('TN', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Tr.A.A.', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Italie', $result->getCountry()->getName());
        $this->assertEquals('IT', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(44.987880706787, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(9.442440032959, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(44.958910323795, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(9.3878520826907, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(45.01685108978, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(9.4970279832272, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEmpty($result->getStreetName());
        $this->assertEmpty($result->getPostalCode());
        $this->assertEquals('Castelnuovo', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('PC', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Em.Rom.', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Italie', $result->getCountry()->getName());
        $this->assertEquals('IT', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(43.82638168335, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(11.068260192871, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(43.797411300357, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(11.014744487393, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(43.855352066342, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(11.121775898349, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEmpty($result->getStreetName());
        $this->assertEmpty($result->getPostalCode());
        $this->assertEquals('Castelnuovo', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('PO', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Tosc.', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Italie', $result->getCountry()->getName());
        $this->assertEquals('IT', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(42.295810699463, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(13.626440048218, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(42.26684031647, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(13.574242599134, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(42.324781082455, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(13.678637497301, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEmpty($result->getStreetName());
        $this->assertEmpty($result->getPostalCode());
        $this->assertEquals('Castelnuovo', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('AQ', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Abr.', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Italie', $result->getCountry()->getName());
        $this->assertEquals('IT', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://dev.virtualearth.net/REST/v1/Locations/1.000000,2.000000?key=api_key&incl=ciso2".
     */
    public function testReverse()
    {
        $provider = new BingMaps($this->getMockAdapter(), 'api_key');
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://dev.virtualearth.net/REST/v1/Locations/48.863216,2.388772?key=api_key&incl=ciso2".
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

        $provider = new BingMaps($this->getAdapter($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY']);
        $results  = $provider->reverse(48.86321648955345, 2.3887719959020615);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.86321648955345, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(2.3887719959020615, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.859353771983, $result->getBounds()->getSouth(), '', 0.0001);
        $this->assertEquals(2.3809437325833, $result->getBounds()->getWest(), '', 0.0001);
        $this->assertEquals(48.867079207124, $result->getBounds()->getNorth(), '', 0.0001);
        $this->assertEquals(2.3966002592208, $result->getBounds()->getEast(), '', 0.0001);
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
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The BingMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getAdapter($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY']);
        $provider->geocode('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The BingMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMaps($this->getAdapter($_SERVER['BINGMAPS_API_KEY']), $_SERVER['BINGMAPS_API_KEY']);
        $provider->geocode('::ffff:88.188.221.14');
    }
}
