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
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class MultipleResultFactory implements ResultFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    final public function createFromArray(array $data)
    {
        $result = new \SplObjectStorage();
        foreach ($data as $row) {
            $instance = $this->newInstance();
            $instance->fromArray($row);
            $result->attach($instance);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function newInstance()
    {
        return new Geocoded();
    }
}
