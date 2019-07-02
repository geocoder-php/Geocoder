<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Http\Provider\Tests;

use Geocoder\Collection;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\LookupQuery;
use Geocoder\Query\ReverseQuery;
use Http\Client\HttpClient;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;

class AbstractHttpProviderTest extends TestCase
{
    public function testHttpClientGetter()
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $provider = new DummyProvider($client);
        $this->assertSame($client, $provider->getHttpClient());
    }
}

class DummyProvider extends AbstractHttpProvider
{
    public function getHttpClient(): HttpClient
    {
        return parent::getHttpClient();
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return new AddressCollection([]);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        return new AddressCollection([]);
    }

    public function lookupQuery(LookupQuery $query): Collection
    {
        return new AddressCollection([]);
    }

    public function getName(): string
    {
        return 'dummy';
    }
}
