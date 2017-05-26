<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GeoPlugin;

use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\IpAddressGeocoder;
use Geocoder\Provider\Provider;

/**
 * @author Andrea Cristaudo <andrea.cristaudo@gmail.com>
 */
final class GeoPlugin extends AbstractHttpProvider implements Provider, IpAddressGeocoder
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://www.geoplugin.net/json.gp?ip=%s';

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $address = $query->getText();
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GeoPlugin provider does not support street addresses, only IP addresses.');
        }

        if (in_array($address, ['127.0.0.1', '::1'])) {
            return $this->returnResults([$this->getLocalhostDefaults()]);
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, $address);

        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        throw new UnsupportedOperation('The GeoPlugin provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'geo_plugin';
    }

    /**
     * @param string $url
     */
    private function executeQuery($url)
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);

        if (!is_array($json) || !count($json)) {
            throw InvalidServerResponse::create($url);
        }

        if (!array_key_exists('geoplugin_status', $json) || (200 !== $json['geoplugin_status'] && 206 !== $json['geoplugin_status'])) {
            return new AddressCollection([]);
        }

        $data = array_filter($json);

        $adminLevels = [];

        $region = \igorw\get_in($data, ['geoplugin_regionName']);
        $regionCode = \igorw\get_in($data, ['geoplugin_regionCode']);

        if (null !== $region || null !== $regionCode) {
            $adminLevels[] = ['name' => $region, 'code' => $regionCode, 'level' => 1];
        }

        $results = [];
        $results[] = array_merge($this->getDefaults(), [
                'locality' => isset($data['geoplugin_city']) ? $data['geoplugin_city'] : null,
                'country' => isset($data['geoplugin_countryName']) ? $data['geoplugin_countryName'] : null,
                'countryCode' => isset($data['geoplugin_countryCode']) ? $data['geoplugin_countryCode'] : null,
                'adminLevels' => $adminLevels,
                'latitude' => isset($data['geoplugin_latitude']) ? $data['geoplugin_latitude'] : null,
                'longitude' => isset($data['geoplugin_longitude']) ? $data['geoplugin_longitude'] : null,
        ]);

        return $this->returnResults($results);
    }
}
