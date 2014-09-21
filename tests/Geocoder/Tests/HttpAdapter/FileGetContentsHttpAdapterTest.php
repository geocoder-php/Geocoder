<?php

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\Tests\TestCase;

use Geocoder\HttpAdapter\FileGetContentsHttpAdapter;

/**
 * @author Thijs Scheepers <thijs@label305.com>
 */
class FileGetContentsHttpAdapterTest extends TestCase
{
    protected $fileGetContents;

    protected function setUp()
    {
        $this->fileGetContents = new FileGetContentsHttpAdapter();
    }

    public function testGetNullContent()
    {
        $this->assertNull($this->fileGetContents->getContent(null));
    }

    public function testGetFalseContent()
    {
        $this->assertNull($this->fileGetContents->getContent(false));
    }

    public function testGetName()
    {
        $this->assertEquals('file_get_contents', $this->fileGetContents->getName());
    }
}
