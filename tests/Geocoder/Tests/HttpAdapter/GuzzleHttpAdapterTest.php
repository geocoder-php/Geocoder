<?php

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\HttpAdapter\GuzzleHttpAdapter;

use Guzzle\Http\Message\Response;
use Guzzle\Http\Plugin\HistoryPlugin;
use Guzzle\Service\Plugin\MockPlugin;
use Guzzle\Service\Client;

/**
 * @author Michael Dowling <michael@guzzlephp.org>
 */
class GuzzleHttpAdapterTest extends \Geocoder\Tests\TestCase
{
    /**
     * @covers Geocoder\HttpAdapter\GuzzleHttpAdapter::__construct
     */
    public function testCreatesDefaultClient()
    {
        $adapter = new GuzzleHttpAdapter();
        $this->assertEquals('guzzle', $adapter->getName());
    }

    /**
     * @covers Geocoder\HttpAdapter\GuzzleHttpAdapter::__construct
     * @covers Geocoder\HttpAdapter\GuzzleHttpAdapter::getContent
     */
    public function testRetrievesResponse()
    {
        $historyPlugin = new HistoryPlugin();
        $mockPlugin = new MockPlugin(array(new Response(200, null, 'body')));

        $client = new Client();
        $client->getEventManager()->attach($mockPlugin);
        $client->getEventManager()->attach($historyPlugin);

        $adapter = new GuzzleHttpAdapter($client);
        $this->assertEquals('body', $adapter->getContent('http://test.com/'));

        $this->assertEquals('http://test.com/',
            $historyPlugin->getLastRequest()->getUrl());
    }
}
