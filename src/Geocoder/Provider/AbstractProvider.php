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
use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class AbstractProvider
{
    /**
     * @var HttpAdapterInterface
     */
    protected $adapter = null;

    /**
     * @var string
     */
    protected $locale = null;

    /**
     * @var integer
     */
    protected $maxResults = Geocoder::MAX_RESULTS;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = null)
    {
        $this->setAdapter($adapter);
        $this->setLocale($locale);
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
     * Sets the HTTP adapter to be used for further requests.
     *
     * @param HttpAdapterInterface $adapter
     *
     * @return AbstractProvider
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Returns the configured locale or null.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the locale to be used.
     *
     * @param string|null $locale If no locale is set, the provider or service will fallback.
     *
     * @return AbstractProvider
     */
    public function setLocale($locale = null)
    {
        $this->locale = $locale;

        return $this;
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
            'latitude'      => null,
            'longitude'     => null,
            'bounds'        => null,
            'streetNumber'  => null,
            'streetName'    => null,
            'city'          => null,
            'zipcode'       => null,
            'cityDistrict'  => null,
            'county'        => null,
            'countyCode'    => null,
            'region'        => null,
            'regionCode'    => null,
            'country'       => null,
            'countryCode'   => null,
            'timezone'      => null,
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
            'city'      => 'localhost',
            'region'    => 'localhost',
            'county'    => 'localhost',
            'country'   => 'localhost',
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
}
