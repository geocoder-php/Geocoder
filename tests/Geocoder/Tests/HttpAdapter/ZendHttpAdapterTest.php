<?php

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\Tests\TestCase;

use Geocoder\HttpAdapter\ZendHttpAdapter;
use Zend\Http\Client;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ZendHttpAdapterTest extends TestCase
{
    protected function setUp()
    {
        if (!class_exists('Zend\Http\Client')) {
            $this->markTestSkipped('Zend library has to be installed');
        }
    }

    public function testGetNullContent()
    {
        $zend = new ZendHttpAdapter();
        $this->assertNull($zend->getContent(null));
    }

    public function testGetFalseContent()
    {
        $zend = new ZendHttpAdapter();
        $this->assertNull($zend->getContent(false));
    }

    public function testGetContentWithCustomAdapter()
    {
        $zend = new ZendHttpAdapter();

        try {
            $content = $zend->getContent('http://www.google.fr');
        } catch (\Exception $e) {
            $this->fail('Exception catched: ' . $e->getMessage());
        }

        $this->assertNotNull($content);
        $this->assertContains('google', $content);
    }
}
