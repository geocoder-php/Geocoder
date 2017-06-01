<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\FreeGeoIp;

use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\LocationBuilder;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\IpAddressGeocoder;
use Geocoder\Provider\Provider;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class FreeGeoIp extends AbstractHttpProvider implements Provider, IpAddressGeocoder
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'https://freegeoip.net/json/%s';

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The FreeGeoIp provider does not support street addresses.');
        }

        if (in_array($address, ['127.0.0.1', '::1'])) {
            return new AddressCollection([Address::createFromArray([
                'locality' => 'localhost',
                'country' => 'localhost',
            ])]);
        }

        $content = $this->getUrlContents(sprintf(self::ENDPOINT_URL, $address));
        $data = json_decode($content, true);
        $builder = new LocationBuilder();

        if (!empty($data['region_name']) || !empty($data['region_code'])) {
            $builder->addAdminLevel(1, $data['region_name'] ?? null, $data['region_code'] ?? null);
        }

        $builder->setCoordinates($data['latitude'] ?? null, $data['longitude'] ?? null);
        $builder->setLocality($data['city'] ?? null);
        $builder->setPostalCode($data['zip_code'] ?? null);
        $builder->setCountry($data['country_name'] ?? null);
        $builder->setCountryCode($data['country_code'] ?? null);
        $builder->setTimezone($data['time_zone'] ?? null);

        return new AddressCollection([$builder->build()]);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The FreeGeoIp provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'free_geo_ip';
    }
}
