<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GoogleMapsBusinessProvider;

class GoogleMapsBusinessProviderTest extends TestCase
{
    private $testClientId = 'foo';
    private $testPrivateKey = 'bogus';

    public function testGetName()
    {
        $provider = new GoogleMapsBusinessProvider($this->getMockAdapter($this->never()), $this->testClientId);
        $this->assertEquals('google_maps_business', $provider->getName());
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=foobar&sensor=false
     */
    public function testGetGeocodedData()
    {
        $provider = new GoogleMapsBusinessProvider($this->getMockAdapter(), $this->testClientId);
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=10+avenue+Gambetta%2C+Paris%2C+France&sensor=false&client=foo&signature=z_AbLZ6L0Pr_JV3aCdtGWXI7Q8Y=
     */
    public function testGetGeocodedDataWithPrivateKey()
    {
        $provider = new GoogleMapsBusinessProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $this->testClientId, $this->testPrivateKey);
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithPrivateKeyViaGuzzle()
    {
        $expectedExceptionMessage = <<<EOL
Client error response
[status code] 403
[reason phrase] Forbidden
[url] http://maps.googleapis.com/maps/api/geocode/json?address=10%2Bavenue%2BGambetta%252C%2BParis%252C%2BFrance&sensor=false&client=foo&signature=7lP36f0gF4j8NCWqUyiF0VCRAzw
[request] GET /maps/api/geocode/json?address=10%2Bavenue%2BGambetta%252C%2BParis%252C%2BFrance&sensor=false&client=foo&signature=7lP36f0gF4j8NCWqUyiF0VCRAzw HTTP/1.1
Host: maps.googleapis.com
EOL;
        $this->setExpectedException('Guzzle\Http\Exception\ClientErrorResponseException', $expectedExceptionMessage);

        $provider = new GoogleMapsBusinessProvider(new \Geocoder\HttpAdapter\GuzzleHttpAdapter(), $this->testClientId, $this->testPrivateKey);
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }
}
