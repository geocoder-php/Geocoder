<?php
namespace Geocoder\Tests\HttpAdapter;

use Geocoder\HttpAdapter\StreamAdapter;
use Geocoder\Tests\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class StreamAdapterTest extends TestCase
{

    protected function setUp()
    {
        $this->adapter = new StreamAdapter();
    }

    public function testGetContent()
    {
        $content = $this->adapter->getContent('http://www.google.de');
        $this->assertNotNull($content);
        $this->assertContains('google', $content);
    }

}
