<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Result;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
interface ResultFactoryInterface
{
    /**
     * @param array $data An array of data.
     *
     * @return ResultInterface
     */
    public function createFromArray(array $data);

    /**
     * @return ResultInterface
     */
    public function newInstance();
}
