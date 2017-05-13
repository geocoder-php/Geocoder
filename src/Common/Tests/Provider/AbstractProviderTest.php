<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\AbstractProvider;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AbstractProviderTest extends TestCase
{
    public function testGetLocalhostDefaults()
    {
        $client = $this->prophesize('Http\Client\HttpClient');
        $provider = new MockProvider($client->reveal());
        $result = $provider->getLocalhostDefaults();

        $this->assertEquals(2, count($result));
        $this->assertEquals('localhost', $result['locality']);
        $this->assertEquals('localhost', $result['country']);
    }
}

class MockProvider extends AbstractProvider
{
    public function getLocalhostDefaults()
    {
        return parent::getLocalhostDefaults();
    }
}
