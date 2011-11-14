<?php

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\Tests\TestCase;

use Geocoder\HttpAdapter\CurlHttpAdapter;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class CurlHttpAdapterTest extends TestCase
{
    protected function setUp()
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped('cURL has to be enabled.');
        }
    }

    public function testGetNullContent()
    {
        $curl = new CurlHttpAdapter();
        $this->assertNull($curl->getContent(null));
    }

    public function testGetFalseContent()
    {
        $curl = new CurlHttpAdapter();
        $this->assertNull($curl->getContent(null));
    }
}
