<?php

namespace Geocoder\Tests\Formatter;

use Geocoder\Formatter\StringFormatter;
use Geocoder\Tests\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class StringFormatterTest extends TestCase
{
    private $formatter;

    public function setUp()
    {
        $this->formatter = new StringFormatter();
    }

    /**
     * @dataProvider dataProviderForTestFormat
     */
    public function testFormat($data, $format, $expected)
    {
        $address = $this->createAddress($data);
        $result  = $this->formatter->format($address, $format);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public static function dataProviderForTestFormat()
    {
        return array(
            array(
                array('streetNumber' => 10),
                '%n',
                '10'
            ),
            array(
                array('streetName' => 'Via San Marco'),
                '%S',
                'Via San Marco'
            ),
            array(
                array('locality' => 'Zuerich'),
                '%L',
                'Zuerich'
            ),
            array(
                array('postalCode' => '8001'),
                '%z',
                '8001'
            ),
            array(
                array('county' => 'Collin County'),
                '%P',
                'Collin County'
            ),
            array(
                array('countyCode' => 'FC'),
                '%p',
                'FC'
            ),
            array(
                array('region' => 'Auvergne'),
                '%R',
                'Auvergne'
            ),
            array(
                array('regionCode' => 'CA'),
                '%r',
                'CA'
            ),
            array(
                array('country' => 'France'),
                '%C',
                'France'
            ),
            array(
                array('countryCode' => 'fr'),
                '%c',
                'FR'
            ),
            array(
                array('timezone' => 'Europe/Paris'),
                '%T',
                'Europe/Paris'
            ),
            array(
                array('subLocality' => 'District'),
                '%D',
                'District'
            ),
            array(
                array(
                    'streetNumber' => 120,
                    'streetName'   => 'Badenerstrasse',
                    'postalCode'   => 8001,
                    'locality'     => 'Zuerich',
                ),
                '%S %n, %z %L',
                'Badenerstrasse 120, 8001 Zuerich'
            ),
            array(
                array(
                    'streetNumber' => 120,
                    'streetName'   => 'Badenerstrasse',
                    'postalCode'   => 8001,
                    'locality'     => 'Zuerich',
                ),
                '<p>%S %n, %z <a href="#%L">%L</a></p>',
                '<p>Badenerstrasse 120, 8001 <a href="#Zuerich">Zuerich</a></p>'
            ),
            array(
                array(
                    'streetNumber' => 120,
                    'streetName'   => 'Badenerstrasse',
                    'postalCode'   => 8001,
                    'locality'     => 'Zuerich',
                ),
                '<p>%S %n, %z <a href="#%L">%L</a></p><p>%P</p>',
                '<p>Badenerstrasse 120, 8001 <a href="#Zuerich">Zuerich</a></p><p></p>'
            ),
        );
    }
}
