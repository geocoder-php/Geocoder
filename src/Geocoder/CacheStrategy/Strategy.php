<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\CacheStrategy;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
interface Strategy
{
    function invoke($key, callable $function);
}
