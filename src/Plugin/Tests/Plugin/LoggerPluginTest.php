<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin\Tests\Plugin;

use Geocoder\Exception\QuotaExceeded;
use Geocoder\Model\AddressCollection;
use Geocoder\Plugin\Plugin\LoggerPlugin;
use Geocoder\Plugin\PluginProvider;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

class LoggerPluginTest extends TestCase
{
    public function testPlugin()
    {
        $logger = $this->getMockBuilder(AbstractLogger::class)
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();
        $logger->expects($this->once())
            ->method('log')
            ->with('info', $this->callback(function ($message) {
                return false !== strstr($message, 'Got 0 results');
            }));

        $geocodeQuery = GeocodeQuery::create('foo');
        $collection = new AddressCollection([]);

        $provider = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->setMethods(['geocodeQuery', 'reverseQuery', 'lookupQuery', 'getName'])
            ->getMock();
        $provider->expects($this->once())
            ->method('geocodeQuery')
            ->with($geocodeQuery)
            ->willReturn($collection);
        $provider->expects($this->never())->method('reverseQuery');
        $provider->expects($this->never())->method('lookupQuery');
        $provider->expects($this->never())->method('getName');

        $pluginProvider = new PluginProvider($provider, [new LoggerPlugin($logger)]);
        $this->assertSame($collection, $pluginProvider->geocodeQuery($geocodeQuery));
    }

    public function testPluginException()
    {
        $this->expectException(QuotaExceeded::class);
        $logger = $this->getMockBuilder(AbstractLogger::class)
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();
        $logger->expects($this->once())
            ->method('log')
            ->with('error', $this->callback(function ($message) {
                return false !== strstr($message, 'QuotaExceeded');
            }));

        $geocodeQuery = GeocodeQuery::create('foo');
        $provider = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->setMethods(['geocodeQuery', 'reverseQuery', 'lookupQuery', 'getName'])
            ->getMock();
        $provider->expects($this->once())
            ->method('geocodeQuery')
            ->willThrowException(new QuotaExceeded());
        $provider->expects($this->never())->method('reverseQuery');
        $provider->expects($this->never())->method('lookupQuery');
        $provider->expects($this->never())->method('getName');

        $pluginProvider = new PluginProvider($provider, [new LoggerPlugin($logger)]);
        $pluginProvider->geocodeQuery($geocodeQuery);
    }
}
