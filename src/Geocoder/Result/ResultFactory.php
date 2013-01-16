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
 * @author William Durand <william.durand1@gmail.com>
 */
class ResultFactory
{
    /**
     * @param array $data An array of data.
     *
     * @return ResultInterface
     */
    final public function createFromArray(array $data)
    {
        $result = $this->newInstance();
        $result->fromArray($data);

        return $result;
    }

    /**
     * @return ResultInterface
     */
    public function newInstance()
    {
        return new Geocoded();
    }
}
