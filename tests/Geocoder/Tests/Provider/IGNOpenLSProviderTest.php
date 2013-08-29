<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\IGNOpenLSProvider;

class IGNOpenLSProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('ign_openls', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter($this->never()), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=xml&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%225%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3Efoobar%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedData()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=xml&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%225%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3E%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=xml&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%225%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3E%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=xml&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%225%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3E36+Quai+des+Orf%C3%A8vres%2C+Paris%2C+France%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedMessage Could not execute query http://gpp3-wxs.ign.fr/jqljhusfrcl0c3w1int0dzhd/geoportail/ols?output=xml&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%225%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3Efoobar%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithAddressGetsErrorContent()
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<XLS version="1.2" xsi:schemaLocation="http://gpp3-wxs.ign.fr/schemas/olsAll.xsd" xmlns:xls="http://www.opengis.net/xls" xmlns="http://www.opengis.net/xls" xmlns:xlsext="http://www.opengis.net/xlsext" xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <ResponseHeader/>
    <Response version="1.2">
        <GeocodeResponse>
            <GeocodeResponseList numberOfGeocodedAddresses="0"/>
        </GeocodeResponse>
    </Response>
</XLS>
XML;

        $provider = new IGNOpenLSProvider($this->getMockAdapterReturns($xml), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    public function testGetGeocodedDataReturnsMultipleResults()
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<XLS version="1.2" xsi:schemaLocation="http://gpp3-wxs.ign.fr/schemas/olsAll.xsd" xmlns:xls="http://www.opengis.net/xls" xmlns="http://www.opengis.net/xls" xmlns:xlsext="http://www.opengis.net/xlsext" xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <ResponseHeader/>
    <Response version="1.2">
        <GeocodeResponse>
            <GeocodeResponseList numberOfGeocodedAddresses="5">
                <GeocodedAddress>
                    <gml:Point>
                        <gml:pos>48.855471 2.343021</gml:pos>
                    </gml:Point>
                    <Address countryCode="StreetAddress">
                        <StreetAddress>
                            <Building number="36"/>
                            <Street>qu des orfevres</Street>
                        </StreetAddress>
                        <Place type="Municipality">Paris</Place>
                        <Place type="Qualite">Plaque adresse</Place>
                        <Place type="Departement">75</Place>
                        <Place type="Commune">Paris</Place>
                        <Place type="Territoire">FXX</Place>
                        <PostalCode>75001</PostalCode>
                    </Address>
                    <GeocodeMatchCode matchType="Street number" accuracy="0.8039562614314194"/>
                </GeocodedAddress>
                <GeocodedAddress>
                    <gml:Point>
                        <gml:pos>48.858515 2.345263</gml:pos>
                    </gml:Point>
                    <Address countryCode="StreetAddress">
                        <StreetAddress>
                            <Street>r des orfevres</Street>
                        </StreetAddress>
                        <Place type="Municipality">Paris</Place>
                        <Place type="Qualite">2.5</Place>
                        <Place type="Departement">75</Place>
                        <Place type="Bbox">2.345118;48.858219;2.345409;48.858812</Place>
                        <Place type="Commune">Paris</Place>
                        <Place type="Territoire">FXX</Place>
                        <PostalCode>75001</PostalCode>
                    </Address>
                    <GeocodeMatchCode matchType="Street" accuracy="0.6479110861835901"/>
                </GeocodedAddress>
                <GeocodedAddress>
                    <gml:Point>
                        <gml:pos>48.856667 2.349042</gml:pos>
                    </gml:Point>
                    <Address countryCode="StreetAddress">
                        <StreetAddress>
                            <Street>qu de gesvres</Street>
                        </StreetAddress>
                        <Place type="Municipality">Paris</Place>
                        <Place type="Qualite">2.5</Place>
                        <Place type="Departement">75</Place>
                        <Place type="Bbox">2.348926;48.856494;2.349495;48.856712</Place>
                        <Place type="Commune">Paris</Place>
                        <Place type="Territoire">FXX</Place>
                        <PostalCode>75004</PostalCode>
                    </Address>
                    <GeocodeMatchCode matchType="Street" accuracy="0.5286360630901586"/>
                </GeocodedAddress>
                <GeocodedAddress>
                    <gml:Point>
                        <gml:pos>48.835584 2.278107</gml:pos>
                    </gml:Point>
                    <Address countryCode="StreetAddress">
                        <StreetAddress>
                            <Street>av de la porte de sevres</Street>
                        </StreetAddress>
                        <Place type="Municipality">Paris</Place>
                        <Place type="Qualite">1.5</Place>
                        <Place type="Departement">75</Place>
                        <Place type="Bbox">2.277803;48.834961;2.278267;48.836012</Place>
                        <Place type="Commune">Paris</Place>
                        <Place type="Territoire">FXX</Place>
                        <PostalCode>75015</PostalCode>
                    </Address>
                    <GeocodeMatchCode matchType="Street" accuracy="0.41724411983729437"/>
                </GeocodedAddress>
                <GeocodedAddress>
                    <gml:Point>
                        <gml:pos>48.849953 2.323454</gml:pos>
                    </gml:Point>
                    <Address countryCode="StreetAddress">
                        <StreetAddress>
                            <Building number="36"/>
                            <Street>r de sevres</Street>
                        </StreetAddress>
                        <Place type="Municipality">Paris</Place>
                        <Place type="Qualite">Tronçon</Place>
                        <Place type="Departement">75</Place>
                        <Place type="Commune">Paris</Place>
                        <Place type="Territoire">FXX</Place>
                        <PostalCode>75007</PostalCode>
                    </Address>
                    <GeocodeMatchCode matchType="Street number" accuracy="0.4161277782207547"/>
                </GeocodedAddress>
            </GeocodeResponseList>
        </GeocodeResponse>
    </Response>
</XLS>
XML;

        $provider = new IGNOpenLSProvider($this->getMockAdapterReturns($xml), 'api_key');
        $results  = $provider->getGeocodedData('36 Quai des Orfèvres, 75001 Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.855471, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(2.343021, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertEquals(48.858812, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.345409, $results[0]['bounds']['east'], '', 0.01);
        $this->assertEquals(48.858219, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.345118, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(36, $results[0]['streetNumber']);
        $this->assertEquals('qu des orfevres', $results[0]['streetName']);
        $this->assertEquals('qu des orfevres', $results[0]['cityDistrict']);
        $this->assertEquals(75001, $results[0]['zipcode']);
        $this->assertEquals('Paris', $results[0]['city']);

        // hard-coded
        $this->assertEquals('France', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);
        $this->assertEquals('Europe/Paris', $results[0]['timezone']);

        // not provided
        $this->assertNull($results[0]['region']);
        $this->assertNull($results[0]['regionCode']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(48.858515, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(2.345263, $results[1]['longitude'], '', 0.01);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertEquals(48.856712, $results[1]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.349495, $results[1]['bounds']['east'], '', 0.01);
        $this->assertEquals(48.856494, $results[1]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.348926, $results[1]['bounds']['west'], '', 0.01);
        $this->assertEquals(36, $results[1]['streetNumber']);
        $this->assertEquals('r des orfevres', $results[1]['streetName']);
        $this->assertEquals('r des orfevres', $results[1]['cityDistrict']);
        $this->assertEquals(75001, $results[1]['zipcode']);
        $this->assertEquals('Paris', $results[1]['city']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(48.856667, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(2.349042, $results[2]['longitude'], '', 0.01);
        $this->assertArrayHasKey('north', $results[2]['bounds']);
        $this->assertArrayHasKey('east', $results[2]['bounds']);
        $this->assertArrayHasKey('south', $results[2]['bounds']);
        $this->assertArrayHasKey('west', $results[2]['bounds']);
        $this->assertEquals(48.836012, $results[2]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.278267, $results[2]['bounds']['east'], '', 0.01);
        $this->assertEquals(48.834961, $results[2]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.277803, $results[2]['bounds']['west'], '', 0.01);
        $this->assertnull($results[2]['streetNumber']);
        $this->assertEquals('qu de gesvres', $results[2]['streetName']);
        $this->assertEquals('qu de gesvres', $results[2]['cityDistrict']);
        $this->assertEquals(75004, $results[2]['zipcode']);
        $this->assertEquals('Paris', $results[2]['city']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(48.835584, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(2.278107, $results[3]['longitude'], '', 0.01);
        $this->assertNull($results[3]['bounds']);
        $this->assertnull($results[3]['streetNumber']);
        $this->assertEquals('av de la porte de sevres', $results[3]['streetName']);
        $this->assertEquals('av de la porte de sevres', $results[3]['cityDistrict']);
        $this->assertEquals(75015, $results[3]['zipcode']);
        $this->assertEquals('Paris', $results[3]['city']);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(48.849953, $results[4]['latitude'], '', 0.01);
        $this->assertEquals(2.323454, $results[4]['longitude'], '', 0.01);
        $this->assertNull($results[4]['bounds']);
        $this->assertnull($results[4]['streetNumber']);
        $this->assertEquals('r de sevres', $results[4]['streetName']);
        $this->assertEquals('r de sevres', $results[4]['cityDistrict']);
        $this->assertEquals(75007, $results[4]['zipcode']);
        $this->assertEquals('Paris', $results[4]['city']);
    }

    public function testGetGeocodedDataReturnsSingleResult()
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<XLS version="1.2" xsi:schemaLocation="http://gpp3-wxs.ign.fr/schemas/olsAll.xsd" xmlns:xls="http://www.opengis.net/xls" xmlns="http://www.opengis.net/xls" xmlns:xlsext="http://www.opengis.net/xlsext" xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <ResponseHeader/>
    <Response version="1.2">
        <GeocodeResponse>
            <GeocodeResponseList numberOfGeocodedAddresses="1">
                <GeocodedAddress>
                    <gml:Point>
                        <gml:pos>49.100976 6.216957</gml:pos>
                    </gml:Point>
                    <Address countryCode="StreetAddress">
                        <StreetAddress>
                            <Street>r marconi</Street>
                        </StreetAddress>
                        <Place type="Municipality">Metz</Place>
                        <Place type="Qualite">2.5</Place>
                        <Place type="Departement">57</Place>
                        <Place type="Bbox">6.215960;49.100595;6.217397;49.102511</Place>
                        <Place type="Commune">Metz</Place>
                        <Place type="Territoire">FXX</Place>
                        <PostalCode>57000</PostalCode>
                    </Address>
                    <GeocodeMatchCode matchType="Street" accuracy="1.0"/>
                </GeocodedAddress>
            </GeocodeResponseList>
        </GeocodeResponse>
    </Response>
</XLS>
XML;

        $provider = new IGNOpenLSProvider($this->getMockAdapterReturns($xml), 'api_key');
        $results  = $provider->getGeocodedData('Rue Marconi 57000 Metz');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(49.100976, $result['latitude'], '', 0.01);
        $this->assertEquals(6.216957, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertEquals(49.102511, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(6.217397, $result['bounds']['east'], '', 0.01);
        $this->assertEquals(49.100595, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(6.215960, $result['bounds']['west'], '', 0.01);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('r marconi', $result['streetName']);
        $this->assertEquals(57000, $result['zipcode']);
        $this->assertEquals('Metz', $result['city']);

        // hard-coded
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
        $this->assertEquals('Europe/Paris', $result['timezone']);

        // not provided
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
    }

    public function testGetGeocodedDataWithRealAddressReturnsMultipleResults()
    {
        if (!isset($_SERVER['IGN_WEB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IGN_WEB_API_KEY value in phpunit.xml');
        }

        $provider = new IGNOpenLSProvider($this->getAdapter(), $_SERVER['IGN_WEB_API_KEY']);
        $results  = $provider->getGeocodedData('36 Quai des Orfèvres, 75001 Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.855471, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(2.343021, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertEquals(48.858812, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.345409, $results[0]['bounds']['east'], '', 0.01);
        $this->assertEquals(48.858219, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.345118, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(36, $results[0]['streetNumber']);
        $this->assertEquals('qu des orfevres', $results[0]['streetName']);
        $this->assertEquals('qu des orfevres', $results[0]['cityDistrict']);
        $this->assertEquals(75001, $results[0]['zipcode']);
        $this->assertEquals('Paris', $results[0]['city']);

        // hard-coded
        $this->assertEquals('France', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);
        $this->assertEquals('Europe/Paris', $results[0]['timezone']);

        // not provided
        $this->assertNull($results[0]['region']);
        $this->assertNull($results[0]['regionCode']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(48.858515, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(2.345263, $results[1]['longitude'], '', 0.01);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertEquals(48.856712, $results[1]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.349495, $results[1]['bounds']['east'], '', 0.01);
        $this->assertEquals(48.856494, $results[1]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.348926, $results[1]['bounds']['west'], '', 0.01);
        $this->assertEquals(36, $results[1]['streetNumber']);
        $this->assertEquals('r des orfevres', $results[1]['streetName']);
        $this->assertEquals('r des orfevres', $results[1]['cityDistrict']);
        $this->assertEquals(75001, $results[1]['zipcode']);
        $this->assertEquals('Paris', $results[1]['city']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(48.856667, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(2.349042, $results[2]['longitude'], '', 0.01);
        $this->assertArrayHasKey('north', $results[2]['bounds']);
        $this->assertArrayHasKey('east', $results[2]['bounds']);
        $this->assertArrayHasKey('south', $results[2]['bounds']);
        $this->assertArrayHasKey('west', $results[2]['bounds']);
        $this->assertEquals(48.836012, $results[2]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.278267, $results[2]['bounds']['east'], '', 0.01);
        $this->assertEquals(48.834961, $results[2]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.277803, $results[2]['bounds']['west'], '', 0.01);
        $this->assertnull($results[2]['streetNumber']);
        $this->assertEquals('qu de gesvres', $results[2]['streetName']);
        $this->assertEquals('qu de gesvres', $results[2]['cityDistrict']);
        $this->assertEquals(75004, $results[2]['zipcode']);
        $this->assertEquals('Paris', $results[2]['city']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(48.835584, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(2.278107, $results[3]['longitude'], '', 0.01);
        $this->assertNull($results[3]['bounds']);
        $this->assertnull($results[3]['streetNumber']);
        $this->assertEquals('av de la porte de sevres', $results[3]['streetName']);
        $this->assertEquals('av de la porte de sevres', $results[3]['cityDistrict']);
        $this->assertEquals(75015, $results[3]['zipcode']);
        $this->assertEquals('Paris', $results[3]['city']);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(48.849953, $results[4]['latitude'], '', 0.01);
        $this->assertEquals(2.323454, $results[4]['longitude'], '', 0.01);
        $this->assertNull($results[4]['bounds']);
        $this->assertnull($results[4]['streetNumber']);
        $this->assertEquals('r de sevres', $results[4]['streetName']);
        $this->assertEquals('r de sevres', $results[4]['cityDistrict']);
        $this->assertEquals(75007, $results[4]['zipcode']);
        $this->assertEquals('Paris', $results[4]['city']);
    }

    public function testGetGeocodedDataWithRealAddressReturnsSingleResult()
    {
        if (!isset($_SERVER['IGN_WEB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IGN_WEB_API_KEY value in phpunit.xml');
        }

        $provider = new IGNOpenLSProvider($this->getAdapter(), $_SERVER['IGN_WEB_API_KEY']);
        $results  = $provider->getGeocodedData('Rue Marconi 57000 Metz');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(49.100976, $result['latitude'], '', 0.01);
        $this->assertEquals(6.216957, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertEquals(49.102511, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(6.217397, $result['bounds']['east'], '', 0.01);
        $this->assertEquals(49.100595, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(6.215960, $result['bounds']['west'], '', 0.01);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('r marconi', $result['streetName']);
        $this->assertEquals(57000, $result['zipcode']);
        $this->assertEquals('Metz', $result['city']);

        // hard-coded
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
        $this->assertEquals('Europe/Paris', $result['timezone']);

        // not provided
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IGNOpenLSProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IGNOpenLSProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IGNOpenLSProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv4()
    {
        $provider = new IGNOpenLSProvider($this->getAdapter(), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IGNOpenLSProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new IGNOpenLSProvider($this->getAdapter(), 'api_key');
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IGNOpenLSProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getReversedData(array(1, 2));
    }
}
