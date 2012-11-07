<?php

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\Tests\TestCase;
use Geocoder\HttpAdapter\SocketHttpAdapter;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class SocketHttpAdapterTest extends TestCase
{
    protected function setUp()
    {
        $this->adapter = new SocketHttpAdapter();
    }

    public function testGetContent()
    {
        try {
            $content = $this->adapter->getContent('http://www.google.de');
        } catch (\Exception $e) {
            $this->fail('Exception catched: ' . $e->getMessage());
        }

        $this->assertNotNull($content);
        $this->assertContains('google', $content);
    }

    /**
     * NOTE ON REFLECTION:
     * Not a great idea but the alternative would be to create a new class for HTTP parsing or set the method public.
     * I don't like either of these much.
     */
    public function testBuildRequest()
    {
        $method = new \ReflectionMethod(
            $this->adapter, 'buildHttpRequest'
        );

        $method->setAccessible(TRUE);

        $ex_host = 'www.google.com';
        $ex_path = '/';

        $ex_body = array();
        $ex_body[] = "GET $ex_path HTTP/1.1";
        $ex_body[] = "Host: $ex_host";
        $ex_body[] = "Connection: Close";
        $ex_body[] = "User-Agent: Geocoder PHP-Library";
        $ex_body[] = "\r\n";

        $this->assertEquals(
            implode("\r\n", $ex_body), $method->invoke($this->adapter, $ex_path, $ex_host)
        );
    }

    public function testParseHtmlResponse()
    {
        $method = new \ReflectionMethod(
            $this->adapter, 'getParsedHttpResponse'
        );
        $method->setAccessible(TRUE);

        //create a file in memory
        $tempFileHandle = fopen('php://memory', 'r+');

        fwrite($tempFileHandle, 'HTTP/1.1 200 OK
            Date: Mon, 01 Oct 2012 20:58:51 GMT
            Expires: -1
            Cache-Control: private, max-age=0
            Content-Type: text/html; charset=ISO-8859-1
            X-Frame-Options: SAMEORIGIN
            Connection: close

            <!doctype html>
            <html itemscope="itemscope" itemtype="http://schema.org/WebPage">
            <head>
            <title>Foo</title>
            </head>
            <body>
            <p>Bar</p>
            </body>
            </html>
            ');

        //get a parsed response
        rewind($tempFileHandle);
        $httpResponse =  $method->invoke($this->adapter, $tempFileHandle);

        //does it look like what we went it?
        $this->assertEquals('200', $httpResponse['headers']['status']);
        $this->assertEquals('Mon, 01 Oct 2012 20:58:51 GMT', $httpResponse['headers']['date']);
        $this->assertEquals('-1', $httpResponse['headers']['expires']);
        $this->assertEquals('private, max-age=0', $httpResponse['headers']['cache-control']);
        $this->assertEquals('text/html; charset=ISO-8859-1', $httpResponse['headers']['content-type']);
        $this->assertEquals('SAMEORIGIN', $httpResponse['headers']['x-frame-options']);
        $this->assertEquals('close', $httpResponse['headers']['connection']);

        $this->assertContains('<p>Bar</p>', $httpResponse['content']);
    }

    /**
     * @group isolate
     */
    public function testParseJson()
    {
        $method = new \ReflectionMethod(
            $this->adapter, 'getParsedHttpResponse'
        );
        $method->setAccessible(TRUE);

        $tempFileHandle = fopen('php://memory', 'r+');

        fwrite($tempFileHandle, 'HTTP/1.1 200 OK
            Foo: bar
            Baz: cat

    {"foo":"bar","baz":"cat"}
    ');

        //get a parsed response
        rewind($tempFileHandle);
        $httpResponse =  $method->invoke($this->adapter, $tempFileHandle);

        //don't bother testing all this stuff again
        $this->assertEquals('200', $httpResponse['headers']['status']);
        $this->assertEquals('bar', $httpResponse['headers']['foo']);
        $this->assertEquals('cat', $httpResponse['headers']['baz']);

        $this->assertContains('{"foo":"bar","baz":"cat"}', $httpResponse['content']);
    }

}
