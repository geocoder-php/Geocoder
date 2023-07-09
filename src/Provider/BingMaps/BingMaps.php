<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\BingMaps;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Psr\Http\Client\ClientInterface;

/**
 * @author David Guyon <dguyon@gmail.com>
 */
final class BingMaps extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://dev.virtualearth.net/REST/v1/Locations/?maxResults=%d&q=%s&key=%s&incl=ciso2';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://dev.virtualearth.net/REST/v1/Locations/%F,%F?key=%s&incl=ciso2';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param ClientInterface $client An HTTP adapter
     * @param string          $apiKey An API key
     */
    public function __construct(ClientInterface $client, string $apiKey)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        // This API doesn't handle IPs
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The BingMaps provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, $query->getLimit(), urlencode($query->getText()), $this->apiKey);

        return $this->executeQuery($url, $query->getLocale(), $query->getLimit());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $url = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates->getLatitude(), $coordinates->getLongitude(), $this->apiKey);

        return $this->executeQuery($url, $query->getLocale(), $query->getLimit());
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'bing_maps';
    }

    /**
     * @param string $url
     * @param string $locale
     * @param int    $limit
     *
     * @return \Geocoder\Collection
     */
    private function executeQuery(string $url, string $locale = null, int $limit): Collection
    {
        if (null !== $locale) {
            $url = sprintf('%s&culture=%s', $url, str_replace('_', '-', $locale));
        }

        $content = $this->getUrlContents($url);
        $json = json_decode($content);

        if (!isset($json->resourceSets[0]) || !isset($json->resourceSets[0]->resources)) {
            return new AddressCollection([]);
        }

        $data = (array) $json->resourceSets[0]->resources;

        $results = [];
        foreach ($data as $item) {
            $builder = new AddressBuilder($this->getName());
            $coordinates = (array) $item->geocodePoints[0]->coordinates;
            $builder->setCoordinates($coordinates[0], $coordinates[1]);

            if (isset($item->bbox) && is_array($item->bbox) && count($item->bbox) > 0) {
                $builder->setBounds($item->bbox[0], $item->bbox[1], $item->bbox[2], $item->bbox[3]);
            }

            $builder->setStreetName($item->address->addressLine ?? null);
            $builder->setPostalCode($item->address->postalCode ?? null);
            $builder->setLocality($item->address->locality ?? null);
            $builder->setCountry($item->address->countryRegion ?? null);
            $builder->setCountryCode($item->address->countryRegionIso2 ?? null);

            foreach (['adminDistrict', 'adminDistrict2'] as $i => $property) {
                if (property_exists($item->address, $property)) {
                    $builder->addAdminLevel($i + 1, $item->address->{$property}, null);
                }
            }

            $results[] = $builder->build();

            if (count($results) >= $limit) {
                break;
            }
        }

        return new AddressCollection($results);
    }
}
