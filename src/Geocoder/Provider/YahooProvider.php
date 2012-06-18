<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Provider\ProviderInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class YahooProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://where.yahooapis.com/geocode?q=%s&flags=JX&appid=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://where.yahooapis.com/geocode?q=%F,+%F&gflags=R&flags=JX&appid=%s';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param \Geocoder\HttpAdapter\HttpAdapterInterface $adapter
     * @param string                                     $apiKey
     * @param string                                     $locale
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $locale = null)
    {
        parent::__construct($adapter, $locale);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (null === $this->apiKey) {
            throw new \RuntimeException('No API Key provided');
        }

        if ('127.0.0.1' === $address) {
            return $this->getLocalhostDefaults();
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        if (null === $this->apiKey) {
            throw new \RuntimeException('No API Key provided');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1], $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'yahoo';
    }

    /**
     * @param string $query
     * @return array
     */
    protected function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&locale=%s', $query, $this->getLocale());
        }

        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $json = json_decode($content);
        if (isset($json->ResultSet) && isset($json->ResultSet->Results)) {
            $data = (array)$json->ResultSet->Results[0];
        } elseif (isset($json->ResultSet) && isset($json->ResultSet->Result)) {
            $data = (array)$json->ResultSet->Result;
        } else {
            return $this->getDefaults();
        }

        $bounds = null;
        if (isset($data['boundingbox'])) {
            $bounds = array(
                'south' => $data['boundingbox']->south,
                'west'  => $data['boundingbox']->west,
                'north' => $data['boundingbox']->north,
                'east'  => $data['boundingbox']->east
            );
        }

        $zipcode = null;
        if (isset($data['postal'])) {
            $zipcode = $data['postal'];
        }
        if (!$zipcode && isset($data['uzip'])) {
            $zipcode = $data['uzip'];
        }
        if ($zipcode && $parts = preg_split('#-#', $zipcode)) {
            $zipcode = $parts[0];
        }

        return array(
            'latitude'      => isset($data['latitude']) ? $data['latitude'] : null,
            'longitude'     => isset($data['longitude']) ? $data['longitude'] : null,
            'bounds'        => $bounds,
            'streetNumber'  => isset($data['house']) ? $data['house'] : null,
            'streetName'    => isset($data['street']) ? $data['street'] : null,
            'city'          => isset($data['city']) ? $data['city'] : null,
            'zipcode'       => $zipcode,
            'cityDistrict'  => isset($data['neighborhood']) ? $data['neighborhood'] : null,
            'county'        => isset($data['county']) ? $data['county'] : null,
            'region'        => isset($data['state']) ? $data['state'] : null,
            'regionCode'    => null,
            'country'       => isset($data['country']) ? $data['country'] : null,
            'countryCode'   => isset($data['countrycode']) ? $data['countrycode'] : null
        );
    }
}
