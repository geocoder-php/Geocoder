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
use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class BaiduProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://api.map.baidu.com/geocoder?output=json&key=%s&address=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://api.map.baidu.com/geocoder?output=json&key=%s&location=%F,%F';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey)
    {
        parent::__construct($adapter);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The BaiduProvider does not support IP addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->apiKey, rawurlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->apiKey, $coordinates[0], $coordinates[1]);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'baidu';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $data = (array) json_decode($content, true);

        if (empty($data) || false === $data) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        if ('INVALID_KEY' === $data['status']) {
            throw new InvalidCredentialsException('API Key provided is not valid.');
        }

        return array(array_merge($this->getDefaults(), array(
            'latitude'     => isset($data['result']['location']['lat']) ? $data['result']['location']['lat'] : null,
            'longitude'    => isset($data['result']['location']['lng']) ? $data['result']['location']['lng'] : null,
            'streetNumber' => isset($data['result']['addressComponent']['street_number']) ? $data['result']['addressComponent']['street_number'] : null,
            'streetName'   => isset($data['result']['addressComponent']['street']) ? $data['result']['addressComponent']['street'] : null,
            'city'         => isset($data['result']['addressComponent']['city']) ? $data['result']['addressComponent']['city'] : null,
            'cityDistrict' => isset($data['result']['addressComponent']['district']) ? $data['result']['addressComponent']['district'] : null,
            'county'       => isset($data['result']['addressComponent']['province']) ? $data['result']['addressComponent']['province'] : null,
            'countyCode'   => isset($data['result']['cityCode']) ? $data['result']['cityCode'] : null,
        )));
    }
}
