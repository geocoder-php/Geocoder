<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\AlgoliaPlaces\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\CachedResponseClient;
use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Location;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Bounds;
use Geocoder\Model\Coordinates;
use Geocoder\Model\Country;
use Geocoder\Provider\AlgoliaPlaces\AlgoliaPlaces;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected bool $testIpv4 = false;

    protected bool $testIpv6 = false;

    protected bool $testReverse = false;

    protected function createProvider(ClientInterface $httpClient)
    {
        return new AlgoliaPlaces($httpClient, $this->getApiKey(), $this->getAppId());
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    /**
     * This client will make real request if cache was not found.
     *
     * @return CachedResponseClient
     */
    private function getCachedHttpClient()
    {
        try {
            $client = Psr18ClientDiscovery::find();
        } catch (\Http\Discovery\Exception\NotFoundException $e) {
            $client = $this->getMockForAbstractClass(ClientInterface::class);

            $client
                ->expects($this->any())
                ->method('sendRequest')
                ->willThrowException($e);
        }

        return new CachedResponseClient($client, $this->getCacheDir(), $this->getAppId());
    }

    protected function getApiKey(): string
    {
        return $_SERVER['ALGOLIA_API_KEY'];
    }

    protected function getAppId(): string
    {
        return $_SERVER['ALGOLIA_APP_ID'];
    }

    public function testGeocodeQuery(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        if (!$this->testAddress) {
            $this->markTestSkipped('Geocoding address is not supported by this provider');
        }

        $provider = $this->createProvider($this->getCachedHttpClient());
        $query = GeocodeQuery::create('10 Downing Street, Westminster, Greater London, United Kingdom')->withLocale('en');
        $result = $provider->geocodeQuery($query);
        $this->assertWellFormattedResult($result);

        // Check Downing Street
        $location = $result->first();
        $this->assertEqualsWithDelta(51.5033, $location->getCoordinates()->getLatitude(), 0.1, 'Latitude should be in London');
        $this->assertEqualsWithDelta(-0.1276, $location->getCoordinates()->getLongitude(), 0.1, 'Longitude should be in London');
        $this->assertStringContainsString('Downing', $location->getStreetName(), 'Street name should contain "Downing St"');

        if (null !== $streetNumber = $location->getStreetNumber()) {
            $this->assertStringContainsString('10', $streetNumber, 'Street number should contain "10"');
        }
    }

    public function testGeocodeQueryWithNoResults(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        if (!$this->testAddress) {
            $this->markTestSkipped('Geocoding address is not supported by this provider');
        }

        $provider = $this->createProvider($this->getCachedHttpClient());
        $query = GeocodeQuery::create('jsajhgsdkfjhsfkjhaldkadjaslgldasd')->withLocale('en');
        $result = $provider->geocodeQuery($query);
        $this->assertWellFormattedResult($result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseQuery(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }
        if (!$this->testReverse) {
            $this->markTestSkipped('Reverse geocoding address is not supported by this provider');
        }

        $provider = $this->createProvider($this->getCachedHttpClient());

        // Close to the white house
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(38.900206, -77.036991)->withLocale('en'));
        $this->assertWellFormattedResult($result);
    }

    public function testReverseQueryWithNoResults(): void
    {
        if (isset($this->skippedTests[__FUNCTION__])) {
            $this->markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        if (!$this->testReverse) {
            $this->markTestSkipped('Reverse geocoding address is not supported by this provider');
        }

        $provider = $this->createProvider($this->getCachedHttpClient());

        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
        $this->assertEquals(0, $result->count());
    }

    /**
     * Make sure that a result for a Geocoder is well formatted. Be aware that even
     * a Location with no data may be well formatted.
     */
    private function assertWellFormattedResult(Collection $result): void
    {
        $this->assertInstanceOf(
            Collection::class,
            $result,
            'The result must be an instance of a Geocoder\Collection'
        );

        /** @var Location $location */
        foreach ($result as $location) {
            $this->assertInstanceOf(
                Location::class,
                $location,
                'All items in Geocoder\Collection must implement Geocoder\Location'
            );

            $this->assertInstanceOf(
                AdminLevelCollection::class,
                $location->getAdminLevels(),
                'Location::getAdminLevels MUST always return a AdminLevelCollection'
            );
            $arrayData = $location->toArray();
            $this->assertTrue(is_array($arrayData), 'Location::toArray MUST return an array.');
            $this->assertNotEmpty($arrayData, 'Location::toArray cannot be empty.');

            // Verify coordinates
            if (null !== $coords = $location->getCoordinates()) {
                $this->assertInstanceOf(
                    Coordinates::class,
                    $coords,
                    'Location::getCoordinates MUST always return a Coordinates or null'
                );

                // Using "assertNotEmpty" means that we can not have test code where coordinates is on equator or long = 0
                $this->assertNotEmpty($coords->getLatitude(), 'If coordinate object exists it cannot have an empty latitude.');
                $this->assertNotEmpty($coords->getLongitude(), 'If coordinate object exists it cannot have an empty longitude.');
            }

            // Verify bounds
            if (null !== $bounds = $location->getBounds()) {
                $this->assertInstanceOf(
                    Bounds::class,
                    $bounds,
                    'Location::getBounds MUST always return a Bounds or null'
                );

                // Using "assertNotEmpty" means that we can not have test code where coordinates is on equator or long = 0
                $this->assertNotEmpty($bounds->getSouth(), 'If bounds object exists it cannot have an empty values.');
                $this->assertNotEmpty($bounds->getWest(), 'If bounds object exists it cannot have an empty values.');
                $this->assertNotEmpty($bounds->getNorth(), 'If bounds object exists it cannot have an empty values.');
                $this->assertNotEmpty($bounds->getEast(), 'If bounds object exists it cannot have an empty values.');
            }

            // Check country
            if (null !== $country = $location->getCountry()) {
                $this->assertInstanceOf(
                    Country::class,
                    $country,
                    'Location::getCountry MUST always return a Country or null'
                );
                $this->assertFalse(null === $country->getCode() && null === $country->getName(), 'Both code and name cannot be empty');

                if (null !== $country->getCode()) {
                    $this->assertNotEmpty(
                        $location->getCountry()->getCode(),
                        'The Country should not have an empty code.'
                    );
                }

                if (null !== $country->getName()) {
                    $this->assertNotEmpty(
                        $location->getCountry()->getName(),
                        'The Country should not have an empty name.'
                    );
                }
            }
        }
    }
}
