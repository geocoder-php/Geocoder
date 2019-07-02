<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Cache\Tests;

use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Cache\ProviderCache;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\LookupQuery;
use Geocoder\Query\ReverseQuery;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ProviderCacheTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Provider
     */
    private $providerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CacheInterface
     */
    private $cacheMock;

    protected function setUp()
    {
        parent::setUp();

        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->setMethods(['get', 'set', 'delete', 'clear', 'setMultiple', 'getMultiple', 'deleteMultiple', 'has'])

            ->getMock();

        $this->providerMock = $this->getMockBuilder(Provider::class)
            ->setMethods(['getFoo', 'getName', 'geocodeQuery', 'reverseQuery', 'lookupQuery'])
            ->getMock();
    }

    public function testName()
    {
        $this->providerMock->expects($this->once())
            ->method('getName')
            ->willReturn('foo');

        $providerCache = new ProviderCache($this->providerMock, $this->cacheMock);
        $this->assertEquals('foo (cache)', $providerCache->getName());
    }

    public function testMagicFunction()
    {
        $this->providerMock->expects($this->once())
            ->method('getFoo')
            ->willReturn('foo');

        $providerCache = new ProviderCache($this->providerMock, $this->cacheMock);
        $this->assertEquals('foo', $providerCache->getFoo());
    }

    public function testGeocodeMiss()
    {
        $query = GeocodeQuery::create('foo');
        $result = new AddressCollection([Address::createFromArray([])]);
        $ttl = 4711;

        $this->cacheMock->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->cacheMock->expects($this->once())
            ->method('set')
            ->with($this->anything(), $result, $ttl)
            ->willReturn(null);

        $this->providerMock->expects($this->once())
            ->method('geocodeQuery')
            ->with($query)
            ->willReturn($result);

        $providerCache = new ProviderCache($this->providerMock, $this->cacheMock, $ttl);
        $providerCache->geocodeQuery($query);
    }

    public function testGeocodeHit()
    {
        $query = GeocodeQuery::create('foo');
        $result = new AddressCollection([Address::createFromArray([])]);
        $ttl = 4711;

        $this->cacheMock->expects($this->once())
            ->method('get')
            ->willReturn($result);

        $this->cacheMock->expects($this->never())
            ->method('set');

        $this->providerMock->expects($this->never())
            ->method('geocodeQuery');

        $providerCache = new ProviderCache($this->providerMock, $this->cacheMock, $ttl);
        $providerCache->geocodeQuery($query);
    }

    public function testReverseMiss()
    {
        $query = ReverseQuery::fromCoordinates(1, 2);
        $result = new AddressCollection([Address::createFromArray([])]);
        $ttl = 4711;

        $this->cacheMock->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->cacheMock->expects($this->once())
            ->method('set')
            ->with($this->anything(), $result, $ttl)
            ->willReturn(null);

        $this->providerMock->expects($this->once())
            ->method('reverseQuery')
            ->with($query)
            ->willReturn($result);

        $providerCache = new ProviderCache($this->providerMock, $this->cacheMock, $ttl);
        $providerCache->reverseQuery($query);
    }

    public function testReverseHit()
    {
        $query = ReverseQuery::fromCoordinates(1, 2);
        $result = new AddressCollection([Address::createFromArray([])]);
        $ttl = 4711;

        $this->cacheMock->expects($this->once())
            ->method('get')
            ->willReturn($result);

        $this->cacheMock->expects($this->never())
            ->method('set');

        $this->providerMock->expects($this->never())
            ->method('reverseQuery');

        $providerCache = new ProviderCache($this->providerMock, $this->cacheMock, $ttl);
        $providerCache->reverseQuery($query);
    }

    public function testLookupMiss()
    {
        $query = new LookupQuery('1');
        $result = new AddressCollection([Address::createFromArray([])]);
        $ttl = 4711;

        $this->cacheMock->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->cacheMock->expects($this->once())
            ->method('set')
            ->with($this->anything(), $result, $ttl)
            ->willReturn(null);

        $this->providerMock->expects($this->once())
            ->method('lookupQuery')
            ->with($query)
            ->willReturn($result);

        $providerCache = new ProviderCache($this->providerMock, $this->cacheMock, $ttl);
        $providerCache->lookupQuery($query);
    }

    public function testLookupHit()
    {
        $query = new LookupQuery('1');
        $result = new AddressCollection([Address::createFromArray([])]);
        $ttl = 4711;

        $this->cacheMock->expects($this->once())
            ->method('get')
            ->willReturn($result);

        $this->cacheMock->expects($this->never())
            ->method('set');

        $this->providerMock->expects($this->never())
            ->method('reverseQuery');

        $providerCache = new ProviderCache($this->providerMock, $this->cacheMock, $ttl);
        $providerCache->lookupQuery($query);
    }
}
