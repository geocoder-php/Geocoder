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
class DefaultResultFactory implements ResultFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    final public function createFromArray(array $data)
    {
        $results = array();

        if (count($data)>0 && is_array(reset($data))) {
            foreach ($data as $dataGeocode) {
                $results[] = $this->getEntityGeocoded($dataGeocode);
            }
        } else {
            $results[] = $this->getEntityGeocoded($data);;
        }

        return (count($results) == 1) ? $results[0] : $results;
    }

    /**
     * {@inheritDoc}
     */
    public function newInstance()
    {
        return new Geocoded();
    }

    /**
     * {@inheritDoc}
     */
    private function getEntityGeocoded($data)
    {
        $result = $this->newInstance();
        $result->fromArray($data);

        return $result;

    }
}
