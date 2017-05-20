<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests;

use Geocoder\Model\LocationFactory;
use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use Http\Mock\Client as MockClient;
use Http\Client\Curl\Client as HttplugClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 *
 * @deprecated Use geocoder-php/provider-integration-tests instead
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param null|object $expects
     *
     * @return HttpClient
     */
    protected function getMockAdapter($expects = null)
    {
        if (null === $expects) {
            $expects = $this->once();
        }

        $stream = $this->getMockBuilder('Psr\Http\Message\StreamInterface')->getMock();
        $stream
            ->expects($expects)
            ->method('__toString')
            ->will($this->returnValue(''));

        $client = new MockClient();
        $client->addResponse(new Response(200, [], $stream));

        return $client;
    }

    /**
     * @param $returnValue
     *
     * @return HttpClient
     */
    protected function getMockAdapterReturns($returnValue)
    {
        $client = new MockClient();
        $client->addResponse(new Response(200, [], (string) $returnValue));

        return $client;
    }

    /**
     * @param callable $requestCallback
     *
     * @return HttpClient
     */
    protected function getMockAdapterWithRequestCallback(callable $requestCallback)
    {
        $client = $this->getMockForAbstractClass(HttpClient::class);

        $client
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($requestCallback) {
                $response = $requestCallback($request);

                if (!$response instanceof ResponseInterface) {
                    $response = new Response(200, [], (string) $response);
                }

                return $response;
            });

        return $client;
    }

    /**
     * Because I was bored to fix the test suite because of
     * a change in a third-party API...
     *
     * @return HttpClient
     */
    protected function getAdapter($apiKey = null)
    {
        return new CachedResponseClient(
            new HttplugClient(),
            $this->useCache(),
            $apiKey
        );
    }

    /**
     * @return bool
     */
    protected function useCache()
    {
        return isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES'];
    }

    protected function createAddress(array $data)
    {
        return LocationFactory::createLocation($data);
    }

    protected function createEmptyAddress()
    {
        return $this->createAddress([]);
    }
}
