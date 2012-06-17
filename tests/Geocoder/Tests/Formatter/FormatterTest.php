<?php

namespace Geocoder\Tests\Formatter;

use Geocoder\Formatter\Formatter;
use Geocoder\Result\Geocoded;
use Geocoder\Tests\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FormatterTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestFormat
     */
    public function testFormat($data, $format, $expected)
    {
        $geocodedObject = new Geocoded();
        $geocodedObject->fromArray($data);

        $formatter = new Formatter($geocodedObject);
        $result    = $formatter->format($format);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    static public function dataProviderForTestFormat()
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
                array('city' => 'Zuerich'),
                '%T',
                'Zuerich'
            ),
            array(
                array('zipcode' => '8001'),
                '%z',
                '8001'
            ),
            array(
                array('county' => 'Collin County'),
                '%K',
                'Collin County'
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
                'fr'
            ),
            array(
                array(
                    'streetNumber'  => 120,
                    'streetName'    => 'Badenerstrasse',
                    'zipcode'       => 8001,
                    'city'          => 'Zuerich',
                ),
                '%S %n, %z %T',
                'Badenerstrasse 120, 8001 Zuerich'
            ),
            array(
                array(
                    'streetNumber'  => 120,
                    'streetName'    => 'Badenerstrasse',
                    'zipcode'       => 8001,
                    'city'          => 'Zuerich',
                ),
                '<p>%S %n, %z <a href="#%T">%T</a></p>',
                '<p>Badenerstrasse 120, 8001 <a href="#Zuerich">Zuerich</a></p>'
            ),
            array(
                array(
                    'streetNumber'  => 120,
                    'streetName'    => 'Badenerstrasse',
                    'zipcode'       => 8001,
                    'city'          => 'Zuerich',
                ),
                '<p>%S %n, %z <a href="#%T">%T</a></p><p>%K</p>',
                '<p>Badenerstrasse 120, 8001 <a href="#Zuerich">Zuerich</a></p><p></p>'
            ),
        );
    }
}
