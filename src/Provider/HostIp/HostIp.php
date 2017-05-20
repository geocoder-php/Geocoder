<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\HostIp;

use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Collection;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\IpAddressGeocoder;
use Geocoder\Provider\Provider;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class HostIp extends AbstractHttpProvider implements Provider, IpAddressGeocoder
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://api.hostip.info/get_json.php?ip=%s&position=true';

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $address = $query->getText();
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The HostIp provider does not support Street addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The HostIp provider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return $this->returnResults([$this->getLocalhostDefaults()]);
        }

        $url = sprintf(self::ENDPOINT_URL, $address);

        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        throw new UnsupportedOperation('The HostIp provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'host_ip';
    }

    /**
     * @param string $query
     *
     * @return Collection
     */
    private function executeQuery($query)
    {
        $request = $this->getMessageFactory()->createRequest('GET', $query);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();

        $data = json_decode($content, true);

        if (!$data) {
            return new AddressCollection([]);
        }

        return $this->returnResults([
            array_merge($this->getDefaults(), [
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
                'locality' => $data['city'],
                'country' => $data['country_name'],
                'countryCode' => $data['country_code'],
            ]),
        ]);
    }
}
