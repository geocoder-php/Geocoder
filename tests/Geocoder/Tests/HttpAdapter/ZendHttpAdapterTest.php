<?php

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\Tests\TestCase;

use Geocoder\HttpAdapter\ZendHttpAdapter;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ZendHttpAdapterTest extends TestCase
{
    protected function setUp()
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../../../../vendor');

        if (!class_exists('\Zend_Loader_Autoloader')) {
            $this->markTestSkipped('Zend library has to be installed');
        }

        \Zend_Loader_Autoloader::getInstance();
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
        $content = 'foobar content';
        $adapter = new \Zend_Http_Client_Adapter_Test();
        $adapter->setResponse(
            "HTTP/1.1 200 OK"        . "\r\n" .
            "Content-type: text/xml" . "\r\n" .
                                       "\r\n" .
            $content
        );

        $zend = new ZendHttpAdapter($adapter);
        $this->assertEquals($content, $zend->getContent('http://www.example.com'));
    }
}
