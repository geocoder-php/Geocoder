<?php

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\Tests\TestCase;

use Geocoder\HttpAdapter\CurlHttpAdapter;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class CurlHttpAdapterTest extends TestCase
{
    protected $curl;

    protected function setUp()
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped('cURL has to be enabled.');
        }

        $this->curl = new CurlHttpAdapter();
    }

    public function testGetNullContent()
    {
        $this->assertNull($this->curl->getContent(null));
    }

    public function testGetFalseContent()
    {
        $this->assertNull($this->curl->getContent(false));
    }

    public function testGetName()
    {
        $this->assertEquals('curl', $this->curl->getName());
    }
}
