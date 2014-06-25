<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GoogleMapsBusinessProvider;

class GoogleMapsBusinessProviderTest extends TestCase
{
    private $testClientId   = 'foo';

    private $testPrivateKey = 'bogus';

    public function testGetName()
    {
        $provider = new GoogleMapsBusinessProvider($this->getMockAdapter($this->never()), $this->testClientId);
        $this->assertEquals('google_maps_business', $provider->getName());
    }

    public function testBuildQueryWithNoPrivateKey()
    {
        $method = new \ReflectionMethod(
          'Geocoder\Provider\GoogleMapsBusinessProvider', 'buildQuery'
        );

        $method->setAccessible(true);

        $provider = new GoogleMapsBusinessProvider($this->getMockAdapter($this->never()), $this->testClientId);
        $query    = 'http://maps.googleapis.com/maps/api/geocode/json?address=blah';

        $this->assertEquals($query.'&client=foo', $method->invoke($provider, $query));
    }

    public function testBuildQueryWithPrivateKey()
    {
        $method = new \ReflectionMethod(
          'Geocoder\Provider\GoogleMapsBusinessProvider', 'buildQuery'
        );

        $method->setAccessible(true);

        $provider = new GoogleMapsBusinessProvider(
            $this->getMockAdapter($this->never()),
            $this->testClientId,
            $this->testPrivateKey
        );

        $query = 'http://maps.googleapis.com/maps/api/geocode/json?address=blah';

        $this->assertEquals($query.'&client=foo&signature=9G2weMhhd4E2ciR681gp9YabvUg=', $method->invoke($provider, $query));
    }

    public function testSignQuery()
    {
        $method = new \ReflectionMethod(
          'Geocoder\Provider\GoogleMapsBusinessProvider', 'signQuery'
        );

        $method->setAccessible(true);

        $provider = new GoogleMapsBusinessProvider(
            $this->getMockAdapter($this->never()),
            $this->testClientId,
            $this->testPrivateKey
        );

        $query = 'http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France';

        $this->assertEquals($query.'&signature=7oRS85BVVpPUsyrd4MWFGMJNWok=', $method->invoke($provider, $query));
    }

    /**
     * @expectedException Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Invalid client ID / API Key https://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&client=foo&signature=9dJq1hPF7_iwafUpnqXUqEkP0gY=
     */
    public function testGetGeocodedDataWithInvalidClientIdAndKey()
    {
        $provider = new GoogleMapsBusinessProvider($this->getAdapter(), $this->testClientId, $this->testPrivateKey, null, null, true);

        $provider->getGeocodedData('Columbia University');
    }

    /**
     * @expectedException Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Invalid client ID / API Key http://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&client=foo&signature=9dJq1hPF7_iwafUpnqXUqEkP0gY=
     */
    public function testGetGeocodedDataWithINvalidClientIdAndKeyNoSsl()
    {
        $provider = new GoogleMapsBusinessProvider($this->getAdapter(), $this->testClientId, $this->testPrivateKey, null, null, false);

        $provider->getGeocodedData('Columbia University', true);
    }
}
