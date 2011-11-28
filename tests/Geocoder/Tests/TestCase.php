<?php

namespace Geocoder\Tests;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Geocoder\HttpAdapter\HttpAdapterInterface
     */
    protected function getMockAdapter($expects = null)
    {
        if (null === $expects) {
            $expects = $this->once();
        }

        $mock = $this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface');
        $mock
            ->expects($expects)
            ->method('getContent')
            ->will($this->returnArgument(0));

        return $mock;
    }

    protected function getMockProvider($expects = null)
    {
        if (null === $expects) {
            $expects = $this->once();
        }
        $returnSet = array(
            'latitude'  => null,
            'longitude' => null,
            'city'      => null,
            'zipcode'   => null,
            'region'    => null,
            'country'   => null,
        );
        $mock = $this->getMock('\Geocoder\Provider\ProviderInterface');
        $mock
            ->expects($expects)
            ->method('getGeocodedData')
            ->will($this->returnValue($returnSet));
        $mock
            ->expects($expects)
            ->method('getReversedData')
            ->will($this->returnValue($returnSet));

        $mock
            ->expects($expects)
            ->method('getName')
            ->will($this->returnValue('mock'));
        return $mock;
    }
}
