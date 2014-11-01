<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Model\AddressFactory;
use Geocoder\ProviderBasedGeocoder;
use Ivory\HttpAdapter\HttpAdapterInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class AbstractProvider
{
    /**
     * @var HttpAdapterInterface
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var integer
     */
    protected $maxResults = ProviderBasedGeocoder::MAX_RESULTS;

    /**
     * @var AddressFactory
     */
    protected $factory;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = null)
    {
        $this->setLocale($locale);

        $this->adapter = $adapter;
        $this->factory = new AddressFactory();
    }

    /**
     * Returns the HTTP adapter.
     *
     * @return HttpAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * Returns the maximum of wished results.
     *
     * @return integer
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * Returns the default results.
     *
     * @return array
     */
    protected function getDefaults()
    {
        return array(
            'latitude'     => null,
            'longitude'    => null,
            'bounds'       => null,
            'streetNumber' => null,
            'streetName'   => null,
            'locality'     => null,
            'postalCode'   => null,
            'subLocality'  => null,
            'county'       => null,
            'countyCode'   => null,
            'region'       => null,
            'regionCode'   => null,
            'country'      => null,
            'countryCode'  => null,
            'timezone'     => null,
        );
    }

    /**
     * Returns the results for the 'localhost' special case.
     *
     * @return array
     */
    protected function getLocalhostDefaults()
    {
        return array(
            'locality' => 'localhost',
            'region'   => 'localhost',
            'county'   => 'localhost',
            'country'  => 'localhost',
        );
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

    /**
     * @param array $data An array of data.
     *
     * @return \Geocoder\Model\Address[]
     */
    protected function returnResult(array $data = [])
    {
        return $this->factory->createFromArray($data);
    }
}
