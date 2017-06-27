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

use Geocoder\Collection;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class FreeGeoIp extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param HttpClient $client
     * @param string     $baseUrl
     */
    public function __construct(HttpClient $client, string $baseUrl = 'https://freegeoip.net/json/%s')
    {
        parent::__construct($client);

        $this->baseUrl = $baseUrl;
    }

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
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        $content = $this->getUrlContents(sprintf($this->baseUrl, $address));
        $data = json_decode($content, true);
        $builder = new AddressBuilder($this->getName());

        if (!empty($data['region_name'])) {
            $builder->addAdminLevel(1, $data['region_name'], $data['region_code'] ?? null);
        }

        if ($data['latitude'] !== 0 || $data['longitude'] !== 0) {
            $builder->setCoordinates($data['latitude'] ?? null, $data['longitude'] ?? null);
        }
        $builder->setLocality(empty($data['city']) ? null : $data['city']);
        $builder->setPostalCode(empty($data['zip_code']) ? null : $data['zip_code']);
        $builder->setCountry(empty($data['country_name']) ? null : $data['country_name']);
        $builder->setCountryCode(empty($data['country_code']) ? null : $data['country_code']);
        $builder->setTimezone(empty($data['time_zone']) ? null : $data['time_zone']);

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
