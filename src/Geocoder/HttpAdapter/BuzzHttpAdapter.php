<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\HttpAdapter;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class BuzzHttpAdapter implements HttpAdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getContent($url)
    {
        $browser = new \Buzz\Browser();
        $response = $browser->get($url);

        return $response->getContent();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'buzz';
    }
}
