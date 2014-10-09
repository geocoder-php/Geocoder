<?php

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\HttpAdapter\Guzzle4HttpAdapter;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Client;

/**
 * @author Michael Dowling <michael@guzzlephp.org>
 */
class Guzzle4HttpAdapterTest extends \Geocoder\Tests\TestCase
{
    protected function setUp()
    {
        if (!class_exists('GuzzleHttp\Client')) {
            $this->markTestSkipped('Guzzle library has to be installed');
        }
    }

    public function testGetName()
    {
        $adapter = new Guzzle4HttpAdapter();
        $this->assertEquals('guzzle4', $adapter->getName());
    }

    /**
     * @covers Geocoder\HttpAdapter\GuzzleHttpAdapter::__construct
     * @covers Geocoder\HttpAdapter\GuzzleHttpAdapter::getContent
     */
    public function testRetrievesResponse()
    {
        $historyPlugin = new History();
        $mockPlugin = new Mock(array(new Response(200, array(), Stream::factory('body'))));

        $client = new Client();
        $client->getEmitter()->attach($mockPlugin);
        $client->getEmitter()->attach($historyPlugin);

        $adapter = new Guzzle4HttpAdapter($client);
        $this->assertEquals('body', $adapter->getContent('http://test.com/'));

        $this->assertEquals('http://test.com/',
            $historyPlugin->getLastRequest()->getUrl());
    }
}
