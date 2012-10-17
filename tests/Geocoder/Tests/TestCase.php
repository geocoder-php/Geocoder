<?php

namespace Geocoder\Tests;

use Geocoder\Exception\InvalidCredentialsException;

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

    /**
     * @return \Geocoder\Provider\ProviderInterface
     */
    protected function getMockProvider($method = null, $with = null)
    {
        $mock = $this->getMock('\Geocoder\Provider\ProviderInterface');

        if (null !== $method && null !== $with) {
            $mock
                ->expects($this->once())
                ->method($method)
                ->with($with)
                ->will($this->returnValue(array('foo' => 'bar')));
        }

        return $mock;
    }

    /**
     * @return \Geocoder\Provider\ProviderInterface
     */
    protected function getMockProviderThrowException($method)
    {
        $mock = $this->getMockProvider();
        $mock
            ->expects($this->once())
            ->method($method)
            ->will($this->returnCallback(function() { throw new \Exception; }));

        return $mock;
    }

    /**
     * @return \Geocoder\Provider\ProviderInterface
     */
    protected function getMockProviderThrowInvalidCredentialsException($method)
    {
        $mock = $this->getMockProvider();
        $mock
            ->expects($this->once())
            ->method($method)
            ->will($this->returnCallback(function() { throw new InvalidCredentialsException('No API Key provided'); }));

        return $mock;
    }
}
