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
class MaxMindOmniProvider extends AbstractMaxMindProvider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geoip.maxmind.com/e?l=%s&i=%s';

    /**
     * @var boolean
     */
    const MAXMIND_SUPPORTS_IPV6 = false;

    /**
     * @var integer
     */
    const MAXMIND_EXPECTED_CHUNKS = 24;

    /**
     * @param  array $chunks
     *
     * @return array
     */
    protected function mapChunksToArray($chunks)
    {
        return array(
            'countryCode' => $this->chunkValueOrNull($chunks[0]),
            'country'     => $this->chunkValueOrNull($chunks[1]),
            'regionCode'  => $this->chunkValueOrNull($chunks[2]),
            'region'      => $this->chunkValueOrNull($chunks[3]),
            'city'        => $this->chunkValueOrNull($chunks[4]),
            'latitude'    => $this->chunkValueOrNull($chunks[5]),
            'longitude'   => $this->chunkValueOrNull($chunks[6]),
            'timezone'    => $this->chunkValueOrNull($chunks[9]),
            'zipcode'     => $this->chunkValueOrNull($chunks[11]),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'maxmind_omni';
    }
}