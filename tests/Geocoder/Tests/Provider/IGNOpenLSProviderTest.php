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
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=json&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%221%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3Efoobar%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedData()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=json&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%221%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3E%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=json&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%221%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3E%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=json&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%221%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3E36+Quai+des+Orf%C3%A8vres%2C+Paris%2C+France%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new IGNOpenLSProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=json&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%221%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3E36+Quai+des+Orf%C3%A8vres%2C+Paris%2C+France%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithAddressGetsEmptyContent()
    {
        $emptyContent = '{"http":{"status":200,"error":null}, "xml":null}';
        $provider = new IGNOpenLSProvider($this->getMockAdapterReturns($emptyContent), 'api_key');
        $provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=json&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%221%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3E36+Quai+des+Orf%C3%A8vres%2C+Paris%2C+France%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithAddressGetsStatus403Content()
    {
        $status403Content = '{"http":{"status":403,"error":null}, "xml":"<EmptyContent></EmptyContent>"}';
        $provider = new IGNOpenLSProvider($this->getMockAdapterReturns($status403Content), 'api_key');
        $provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://gpp3-wxs.ign.fr/api_key/geoportail/ols?output=json&xls=%3Cxls%3AXLS+xmlns%3Axls%3D%22http%3A%2F%2Fwww.opengis.net%2Fxls%22+version%3D%221.2%22%3E%3Cxls%3ARequestHeader%2F%3E%3Cxls%3ARequest+methodName%3D%22LocationUtilityService%22+version%3D%221.2%22+maximumResponses%3D%221%22%3E%3Cxls%3AGeocodeRequest+returnFreeForm%3D%22false%22%3E%3Cxls%3AAddress+countryCode%3D%22StreetAddress%22%3E%3Cxls%3AfreeFormAddress%3E36+Quai+des+Orf%C3%A8vres%2C+Paris%2C+France%3C%2Fxls%3AfreeFormAddress%3E%3C%2Fxls%3AAddress%3E%3C%2Fxls%3AGeocodeRequest%3E%3C%2Fxls%3ARequest%3E%3C%2Fxls%3AXLS%3E
     */
    public function testGetGeocodedDataWithAddressGetsErrorContent()
    {
        $errorContent = '{"http":{"status":200,"error":"<ErrorContent></ErrorContent>"}, "xml":"<EmptyContent></EmptyContent>"}';
        $provider = new IGNOpenLSProvider($this->getMockAdapterReturns($errorContent), 'api_key');
        $provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddressOne()
    {
        if (!isset($_SERVER['IGN_WEB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IGN_WEB_API_KEY value in phpunit.xml');
        }

        $provider = new IGNOpenLSProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['IGN_WEB_API_KEY']);
        $result = $provider->getGeocodedData('36 Quai des Orfèvres, 75001 Paris, France');

        $this->assertEquals(48.855471, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.343021, $result['longitude'], '', 0.0001);
        $this->assertEquals(36, $result['streetNumber']);
        $this->assertEquals('qu des orfevres', $result['streetName']);
        $this->assertEquals(75001, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);

        // hard-coded
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
        $this->assertEquals('Europe/Paris', $result['timezone']);

        // not provided
        $this->assertNull($result['bounds']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
    }

    public function testGetGeocodedDataWithRealAddressTwo()
    {
        if (!isset($_SERVER['IGN_WEB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IGN_WEB_API_KEY value in phpunit.xml');
        }

        $provider = new IGNOpenLSProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['IGN_WEB_API_KEY']);
        $result = $provider->getGeocodedData('Rue Marconi 57000 Metz');

        $this->assertEquals(49.100976, $result['latitude'], '', 0.0001);
        $this->assertEquals(6.216957, $result['longitude'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('r marconi', $result['streetName']);
        $this->assertEquals(57000, $result['zipcode']);
        $this->assertEquals('Metz', $result['city']);

        // hard-coded
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
        $this->assertEquals('Europe/Paris', $result['timezone']);

        // not provided
        $this->assertNull($result['bounds']);
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
        $provider = new IGNOpenLSProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IGNOpenLSProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new IGNOpenLSProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'api_key');
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
