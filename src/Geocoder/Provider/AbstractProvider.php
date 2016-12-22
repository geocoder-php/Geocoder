<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Geocoder;
use Geocoder\Collection;
use Geocoder\Model\AddressFactory;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class AbstractProvider
{

    /**
     * @var AddressFactory
     */
    private $factory;

    /**
     * @var integer
     */
    private $limit = Provider::MAX_RESULTS;

    public function __construct()
    {
        $this->factory = new AddressFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Returns the default results.
     *
     * @return array
     */
    protected function getDefaults()
    {
        return [
            'latitude'     => null,
            'longitude'    => null,
            'bounds'       => [
                'south' => null,
                'west'  => null,
                'north' => null,
                'east'  => null,
            ],
            'streetNumber' => null,
            'streetName'   => null,
            'locality'     => null,
            'postalCode'   => null,
            'subLocality'  => null,
            'adminLevels'  => [],
            'country'      => null,
            'countryCode'  => null,
            'timezone'     => null,
        ];
    }

    /**
     * Returns the results for the 'localhost' special case.
     *
     * @return array
     */
    protected function getLocalhostDefaults()
    {
        return [
            'locality' => 'localhost',
            'country'  => 'localhost',
        ];
    }

    /**
     * @param array $data An array of data.
     *
     * @return Collection
     */
    protected function returnResults(array $data = [])
    {
        if (0 < $this->getLimit()) {
            $data = array_slice($data, 0, $this->getLimit());
        }

        return $this->factory->createFromArray($data);
    }

    /**
    * @param array $results
    *
    * @return array
    */
    protected function fixEncoding(array $results)
    {
        return array_map(function ($value) {
            return is_string($value) ? utf8_encode($value) : $value;
        }, $results);
    }
}
