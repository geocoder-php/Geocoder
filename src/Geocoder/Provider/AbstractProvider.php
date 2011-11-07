<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class AbstractProvider
{
    /**
     * @var \Geocoder\HttpAdapter\HttpAdapterInterface
     */
    private $adapter = null;

    /**
     * @param \Geocoder\HttpAdapter\HttpAdapterInterface $adapter   An HTTP adapter.
     */
    public function __construct(HttpAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns the HTTP adapter.
     *
     * @return \Geocoder\HttpAdapter\HttpAdapterInterface
     */
    protected function getAdapter()
    {
        return $this->adapter;
    }
}
