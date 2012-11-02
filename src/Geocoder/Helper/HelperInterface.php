<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Helper;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 */
interface HelperInterface
{
    /**
     * Get a short definition of each helper
     *
     * @return string
     */
    public function help();
}

