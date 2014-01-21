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
                array('city' => 'Zuerich'),
                '%L',
                'Zuerich'
            ),
            array(
                array('neighborhood' => 'East Village'),
                '%N',
                'East Village'
            ),
            array(
                array('zipcode' => '8001'),
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
                array('cityDistrict' => 'District'),
                '%D',
                'District'
            ),
            array(
                array(
                    'streetNumber'  => 120,
                    'streetName'    => 'Badenerstrasse',
                    'zipcode'       => 8001,
                    'city'          => 'Zuerich',
                ),
                '%S %n, %z %L',
                'Badenerstrasse 120, 8001 Zuerich'
            ),
            array(
                array(
                    'streetNumber'  => 120,
                    'streetName'    => 'Badenerstrasse',
                    'zipcode'       => 8001,
                    'city'          => 'Zuerich',
                ),
                '<p>%S %n, %z <a href="#%L">%L</a></p>',
                '<p>Badenerstrasse 120, 8001 <a href="#Zuerich">Zuerich</a></p>'
            ),
            array(
                array(
                    'streetNumber'  => '236',
                    'streetName'    => 'W 46th St',
                    'zipcode'       => '10036',
                    'neighborhood'  => 'Times Square',
                    'cityDistrict'  => 'Manhattan',
                    'city'          => 'New York',
                    'region'        => 'New York',
                    'regionCode'    => 'NY',
                    'country'       => 'United States',
                    'countryCode'   => 'US',
                ),
                '%n %S, %N, %D, %L, %z, %R (%r), %C (%c)',
                '236 W 46th St, Times Square, Manhattan, New York, 10036, New York (NY), United States (US)'
            ),
        );
    }
}