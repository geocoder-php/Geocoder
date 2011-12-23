<?php

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\Tests\TestCase;
use Geocoder\HttpAdapter\SocketAdapter;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class SocketAdapterTest extends TestCase
{
    protected function setUp()
    {
        $this->adapter = new SocketAdapter();
    }

    public function testGetContent()
    {
        $content = $this->adapter->getContent('http://www.google.de');
        $this->assertNotNull($content);
        $this->assertContains('google', $content);
    }
}
