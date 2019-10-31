<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests;

use Geocoder\Collection;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\ProviderAggregator;
use Geocoder\Provider\Provider;
use Nyholm\NSA;
use PHPUnit\Framework\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ProviderAggregatorTest extends TestCase
{
    /**
     * @var ProviderAggregator
     */
    protected $geocoder;

    protected function setUp()
    {
        $this->geocoder = new ProviderAggregator();
    }

    public function testGeocode()
    {
        $provider1 = new MockProvider('test1');
        $provider1->result = [Address::createFromArray(['providedBy' => 'p1'])];
        $provider2 = new MockProvider('test2');
        $provider2->result = [Address::createFromArray(['providedBy' => 'p2'])];

        $this->geocoder->registerProvider($provider1);
        $this->geocoder->registerProvider($provider2);

        $result = $this->geocoder->geocode('foo');
        $this->assertEquals('p1', $result->first()->getProvidedBy());
    }

    public function testReverse()
    {
        $provider1 = new MockProvider('test1');
        $provider1->result = [Address::createFromArray(['providedBy' => 'p1'])];
        $provider2 = new MockProvider('test2');
        $provider2->result = [Address::createFromArray(['providedBy' => 'p2'])];

        $this->geocoder->registerProvider($provider1);
        $this->geocoder->registerProvider($provider2);
        $this->geocoder->using('test2');

        $result = $this->geocoder->reverse(0.1, 0.2);
        $this->assertEquals('p2', $result->first()->getProvidedBy());
    }

    public function testRegisterProvider()
    {
        $provider = new MockProvider('test');
        $this->geocoder->registerProvider($provider);

        $this->assertSame(['test' => $provider], NSA::getProperty($this->geocoder, 'providers'));
    }

    public function testRegisterProviders()
    {
        $provider = new MockProvider('test');
        $this->geocoder->registerProviders([$provider]);

        $this->assertSame(['test' => $provider], NSA::getProperty($this->geocoder, 'providers'));
    }

    /**
     * @expectedException \Geocoder\Exception\ProviderNotRegistered
     * @expectedExceptionMessage Provider "non_existant" is not registered, so you cannot use it. Did you forget to register it or made a typo? Registered providers are: test1.
     */
    public function testUsingNonExistantProviderShouldThrowAnException()
    {
        $this->geocoder->registerProvider(new MockProvider('test1'));

        $this->geocoder->using('non_existant');
    }

    /**
     * @expectedException \Geocoder\Exception\ProviderNotRegistered
     */
    public function testUsingAnEmptyProviderNameShouldThrowAnException()
    {
        $this->geocoder->using('');
    }

    public function testGetProviders()
    {
        $provider1 = new MockProvider('test1');
        $provider2 = new MockProvider('test2');

        $this->geocoder->registerProviders([$provider1, $provider2]);
        $result = $this->geocoder->getProviders();

        $expected = [
            'test1' => $provider1,
            'test2' => $provider2,
        ];

        $this->assertSame($expected, $result);
        $this->assertArrayHasKey('test1', $result);
        $this->assertArrayHasKey('test2', $result);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetProvider()
    {
        NSA::invokeMethod($this->geocoder, 'getProvider', GeocodeQuery::create('foo'), [], null);
        $this->fail('getProvider() should throw an exception');
    }

    public function testGetProviderWithMultipleProvidersReturnsTheFirstOne()
    {
        $providers = [
            $provider1 = new MockProvider('test1'),
            $provider2 = new MockProvider('test2'),
            $provider3 = new MockProvider('test3'),
        ];

        $query = GeocodeQuery::create('foo');
        $this->assertSame($provider1, NSA::invokeMethod($this->geocoder, 'getProvider', $query, $providers, null));
        $this->assertSame($provider2, NSA::invokeMethod($this->geocoder, 'getProvider', $query, $providers, $provider2));
    }
}

class MockProvider implements Provider
{
    protected $name;

    public $result = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return $this->returnResult();
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        return $this->returnResult();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLimit()
    {
    }

    public function limit($limit)
    {
        return $this;
    }

    private function returnResult()
    {
        return new AddressCollection($this->result);
    }
}
