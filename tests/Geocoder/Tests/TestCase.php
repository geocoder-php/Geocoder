<?php

namespace Geocoder\Tests;

use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param  null|object          $expects
     * @return HttpAdapterInterface
     */
    protected function getMockAdapter($expects = null)
    {
        if (null === $expects) {
            $expects = $this->once();
        }

        $mock = $this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface');
        $mock
            ->expects($expects)
            ->method('getContent')
            ->will($this->returnArgument(0));

        return $mock;
    }

    /**
     * @param $returnValue
     * @return HttpAdapterInterface
     */
    protected function getMockAdapterReturns($returnValue)
    {
        $mock = $this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface');
        $mock
            ->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($returnValue));

        return $mock;
    }

    /**
     * Because I was bored to fix the test suite because of
     * a change in a third-party API...
     *
     * @return HttpAdapterInterface
     */
    protected function getAdapter()
    {
        return new CachedResponseAdapter(new CurlHttpAdapter(), $this->useCache());
    }

    /**
     * @return boolean
     */
    protected function useCache()
    {
        return isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES'];
    }
}
