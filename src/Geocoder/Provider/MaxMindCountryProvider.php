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
class MaxMindCountryProvider extends AbstractMaxMindProvider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geoip.maxmind.com/a?l=%s&i=%s';

    /**
     * @var boolean
     */
    const MAXMIND_SUPPORTS_IPV6 = true;

    /**
     * @var integer
     */
    const MAXMIND_EXPECTED_CHUNKS = 2;

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
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'maxmind_country';
    }
}