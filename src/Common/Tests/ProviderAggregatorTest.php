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
use Geocoder\Geocoder;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\ProviderAggregator;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;
use PHPUnit\Framework\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ProviderAggregatorTest extends TestCase
{
    /**
     * @var TestableGeocoder
     */
    protected $geocoder;

    protected function setUp()
    {
        $this->geocoder = new TestableGeocoder();
    }

    public function testRegisterProvider()
    {
        $provider = new MockProvider('test');
        $this->geocoder->registerProvider($provider);

        $this->assertSame($provider, $this->geocoder->getProvider());
    }

    public function testRegisterProviders()
    {
        $provider = new MockProvider('test');
        $this->geocoder->registerProviders([$provider]);

        $this->assertSame($provider, $this->geocoder->getProvider());
    }

    public function testUsing()
    {
        $provider1 = new MockProvider('test1');
        $provider2 = new MockProvider('test2');
        $this->geocoder->registerProviders([$provider1, $provider2]);

        $this->assertSame($provider1, $this->geocoder->getProvider());

        $this->geocoder->using('test1');
        $this->assertSame($provider1, $this->geocoder->getProvider());

        $this->geocoder->using('test2');
        $this->assertSame($provider2, $this->geocoder->getProvider());

        $this->geocoder->using('test1');
        $this->assertSame($provider1, $this->geocoder->getProvider());
    }

    /**
     * @expectedException \Geocoder\Exception\ProviderNotRegistered
     */
    public function testUsingNonExistantProviderShouldThrowAnException()
    {
        $this->geocoder->using('non_existant');
    }

    /**
     * @expectedException \Geocoder\Exception\ProviderNotRegistered
     */
    public function testUsingNullShouldThrowAnException()
    {
        $this->geocoder->using(null);
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
        $this->geocoder->getProvider();
        $this->fail('getProvider() should throw an exception');
    }

    public function testGetProviderWithMultipleProvidersReturnsTheFirstOne()
    {
        $this->geocoder->registerProviders([
            $provider1 = new MockProvider('test1'),
            $provider2 = new MockProvider('test2'),
            $provider3 = new MockProvider('test3'),
        ]);

        $this->assertSame($provider1, $this->geocoder->getProvider());
    }

    public function testDefaultMaxResults()
    {
        $this->assertSame(Geocoder::DEFAULT_RESULT_LIMIT, $this->geocoder->getLimit());
    }
}

class MockProvider implements Provider
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return $this->returnResult([]);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        return $this->returnResult([]);
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

    public function returnResult(array $data = [])
    {
    }
}

class MockLocaleAwareProvider extends MockProvider implements LocaleAwareGeocoder
{
}

class MockProviderWithData extends MockProvider
{
    public function geocode($address)
    {
        return $this->returnResult([
            'latitude' => 123,
            'longitude' => 456,
        ]);
    }
}

class MockProviderWithRequestCount extends MockProvider
{
    public $geocodeCount = 0;

    public function geocode($address)
    {
        ++$this->geocodeCount;

        return parent::geocode($address);
    }
}

class TestableGeocoder extends ProviderAggregator
{
    public $countCallGetProvider = 0;

    public function getProvider()
    {
        ++$this->countCallGetProvider;

        return parent::getProvider();
    }
}
