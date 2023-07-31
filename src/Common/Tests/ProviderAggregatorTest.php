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
use Geocoder\Provider\Provider;
use Geocoder\ProviderAggregator;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
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

    protected function setUp(): void
    {
        $this->geocoder = new ProviderAggregator();
    }

    public function testGeocode(): void
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

    public function testReverse(): void
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

    public function testRegisterProvider(): void
    {
        $provider = new MockProvider('test');
        $this->geocoder->registerProvider($provider);

        $this->assertSame(['test' => $provider], NSA::getProperty($this->geocoder, 'providers'));
    }

    public function testRegisterProviders(): void
    {
        $provider = new MockProvider('test');
        $this->geocoder->registerProviders([$provider]);

        $this->assertSame(['test' => $provider], NSA::getProperty($this->geocoder, 'providers'));
    }

    public function testUsingNonExistantProviderShouldThrowAnException(): void
    {
        $this->expectException(\Geocoder\Exception\ProviderNotRegistered::class);
        $this->expectExceptionMessage('Provider "non_existant" is not registered, so you cannot use it. Did you forget to register it or made a typo? Registered providers are: test1.');

        $this->geocoder->registerProvider(new MockProvider('test1'));

        $this->geocoder->using('non_existant');
    }

    public function testUsingAnEmptyProviderNameShouldThrowAnException(): void
    {
        $this->expectException(\Geocoder\Exception\ProviderNotRegistered::class);

        $this->geocoder->using('');
    }

    public function testGetProviders(): void
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

    public function testGetProvider(): void
    {
        $this->expectException(\RuntimeException::class);

        NSA::invokeMethod($this->geocoder, 'getProvider', GeocodeQuery::create('foo'), [], null);
        $this->fail('getProvider() should throw an exception');
    }

    public function testGetProviderWithMultipleProvidersReturnsTheFirstOne(): void
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
    protected string $name;

    /**
     * @var Address[]
     */
    public array $result = [];

    public function __construct(string $name)
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

    public function getLimit(): void
    {
    }

    public function limit(int $limit): self
    {
        return $this;
    }

    private function returnResult(): AddressCollection
    {
        return new AddressCollection($this->result);
    }
}
