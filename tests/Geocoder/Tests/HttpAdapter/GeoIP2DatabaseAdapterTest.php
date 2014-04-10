<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\HttpAdapter;

use Geocoder\HttpAdapter\GeoIP2DatabaseAdapter;
use Geocoder\Tests\TestCase;
use Geocoder\Exception\RuntimeException;
use GeoIp2\Database\Reader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;

/**
 * @author Jens Wiese <jens@howtrueisfalse.de>
 */
class GeoIP2DatabaseAdapterTest extends TestCase
{
    /**
     * @var GeoIP2DatabaseAdapter
     */
    protected $adapter;

    /**
     * {@inheritdoc}
     * @throws RuntimeException
     */
    public static function setUpBeforeClass()
    {
        if (false === class_exists('\GeoIp2\Database\Reader')) {
            throw new RuntimeException("The maxmind's lib 'geoip2/geoip2' is required to run this test.");
        }
    }

    public function setUp()
    {
        $this->adapter = new GeoIP2DatabaseAdapter($this->getDbFile()->url());
        $this->adapter->setDbReader($this->getDbReaderMock());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgumentException
     * @expectedExceptionMessage Given MaxMind database file "/tmp" is not a file.
     */
    public function testDatabaseFileMustBeFile()
    {
        $this->adapter = new GeoIP2DatabaseAdapter('/tmp');
        $this->adapter->setDbReader($this->getDbReaderMock());
    }

    /**
     * @depends testDatabaseFileMustBeFile
     * @expectedException \Geocoder\Exception\InvalidArgumentException
     * @expectedExceptionMessage Given MaxMind database file "vfs://tmpdir/database.mmdb" is not readable.
     */
    public function testDatabaseFileMustBeReadable()
    {
        $this->adapter = new GeoIP2DatabaseAdapter($this->getDbFile()->chmod(0)->url());
        $this->adapter->setDbReader($this->getDbReaderMock());
    }

    public function testGetName()
    {
        $expectedName = 'maxmind_database';
        $this->assertEquals($expectedName, $this->adapter->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgumentException
     * @expectedExceptionMessage must be called with a valid url. Got "127.0.0.1" instead.
     */
    public function testGetContentMustBeCalledWithUrl()
    {
        $url = '127.0.0.1';
        $this->adapter->getContent($url);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgumentException
     * @expectedExceptionMessage URL must contain a valid query-string (a IP address, 127.0.0.1 for instance)
     */
    public function testAddressPassedToReaderMustBeIpAddress()
    {
        $url = 'file://database?not-valid=1';
        $this->adapter->getContent($url);
    }

    public function testIpAddressIsPassedCorrectToReader()
    {
        $dbReader = $this->getDbReaderMock();
        $dbReader
            ->expects($this->once())
            ->method('city')->with('127.0.0.1')
            ->will($this->returnValue($this->getCityModelMock()));

        $this->adapter->setDbReader($dbReader);

        $this->adapter->getContent('file://database?127.0.0.1');
    }

    public function testSettingLocaleIsCorrect()
    {
        $this->assertNull($this->adapter->getLocale());

        $expectedLocale = 'it';
        $this->adapter->setLocale($expectedLocale);

        $this->assertEquals($expectedLocale, $this->adapter->getLocale());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage Database type "geoip2_does_not_exist" not implemented yet.
     */
    public function testUsingNonExistantDatabaseTypesLeadsToException()
    {
        $this->adapter = new GeoIP2DatabaseAdapter($this->getDbFile()->url(), 'geoip2_does_not_exist');
        $this->adapter->setDbReader($this->getDbReaderMock());
        $this->adapter->getContent('file://database?127.0.0.1');
    }

    public function testReaderResponseIsJsonEncoded()
    {
        $cityModel = $this->getCityModelMock();

        $dbReader = $this->getDbReaderMock();
        $dbReader
            ->expects($this->any())
            ->method('city')
            ->will($this->returnValue($cityModel));

        $this->adapter->setDbReader($dbReader);

        $result = $this->adapter->getContent('file://database?127.0.0.1');
        $this->assertJson($result);

        $decodedResult = json_decode($result);
        $this->assertObjectHasAttribute('city', $decodedResult);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDbReaderMock()
    {
        $mock = $this->getMockBuilder('\GeoIp2\Database\Reader')->disableOriginalConstructor()->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCityModelMock()
    {
        $mock = $this->getMockBuilder('\GeoIp2\Model\City')->disableOriginalConstructor()->getMock();
        $mock
            ->expects($this->any())
            ->method('jsonSerialize')
            ->will($this->returnValue(
                array(
                    'city' => array(
                        'geoname_id' => 2911298,
                        'names' => array(
                              'de' => 'Hamburg',
                              'en' => 'Hamburg',
                              'es' => 'Hamburgo',
                              'fr' => 'Hambourg',
                              'ja' => 'ハンブルク',
                              'pt-BR' => 'Hamburgo',
                              'ru' => 'Гамбург',
                              'zh-CN' => '汉堡市',
                        )
                    )
                )
            ));

        return $mock;
    }

    /**
     * Returns virtual db-file
     *
     * @return vfsStreamFile
     */
    protected function getDbFile()
    {
        $filesystem = vfsStream::setup('tmpdir');
        $dbFile = new vfsStreamFile('database.mmdb', 0600);
        $filesystem->addChild($dbFile);

        return $dbFile;
    }

}
 