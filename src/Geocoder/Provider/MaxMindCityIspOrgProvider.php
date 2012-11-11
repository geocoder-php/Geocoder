<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

/**
 * @author Andrea Cristaudo <andrea.cristaudo@gmail.com>
 */
class MaxMindCityIspOrgProvider extends AbstractMaxMindProvider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geoip.maxmind.com/f?l=%s&i=%s';

    /**
     * @var boolean
     */
    const MAXMIND_SUPPORTS_IPV6 = false;

    /**
     * @var integer
     */
    const MAXMIND_EXPECTED_CHUNKS = 11;

    /**
     * @param  array $chunks
     *
     * @return array
     */
    protected function mapChunksToArray($chunks)
    {
        return array(
            'countryCode' => $this->chunkValueOrNull($chunks[0]),
            'country'     => $this->countryCodeToCountryName($this->chunkValueOrNull($chunks[0])),
            'regionCode'  => $this->chunkValueOrNull($chunks[1]),
            'city'        => $this->chunkValueOrNull($chunks[2]),
            'zipcode'     => $this->chunkValueOrNull($chunks[3]),
            'latitude'    => $this->chunkValueOrNull($chunks[4]),
            'longitude'   => $this->chunkValueOrNull($chunks[5]),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'maxmind_city_isp_org';
    }
}