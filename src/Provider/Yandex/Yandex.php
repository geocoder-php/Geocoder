<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Yandex;

use Geocoder\Collection;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Provider\Yandex\Model\YandexAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
final class Yandex extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=%F,%F';

    /**
     * @var string
     */
    private $toponym;

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * @param ClientInterface $client  an HTTP adapter
     * @param string          $toponym toponym biasing only for reverse geocoding (optional)
     * @param string|null     $apiKey  API Key
     */
    public function __construct(ClientInterface $client, string $toponym = null, string $apiKey = null)
    {
        parent::__construct($client);

        $this->toponym = $toponym;
        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Yandex provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($url, $query->getLimit(), $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $url = sprintf(self::REVERSE_ENDPOINT_URL, $longitude, $latitude);

        if (null !== $toponym = $query->getData('toponym', $this->toponym)) {
            $url = sprintf('%s&kind=%s', $url, $toponym);
        }

        return $this->executeQuery($url, $query->getLimit(), $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'yandex';
    }

    /**
     * @param string $url
     * @param int    $limit
     * @param string $locale
     *
     * @return AddressCollection
     */
    private function executeQuery(string $url, int $limit, string $locale = null): AddressCollection
    {
        if (null !== $locale) {
            $url = sprintf('%s&lang=%s', $url, str_replace('_', '-', $locale));
        }

        if (null !== $this->apiKey) {
            $url = sprintf('%s&apikey=%s', $url, $this->apiKey);
        }

        $url = sprintf('%s&results=%d', $url, $limit);
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);

        if (empty($json) || isset($json['error']) ||
            (isset($json['response']) && '0' === $json['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'])
        ) {
            return new AddressCollection([]);
        }

        $data = $json['response']['GeoObjectCollection']['featureMember'];

        $locations = [];
        foreach ($data as $item) {
            $builder = new AddressBuilder($this->getName());
            $bounds = null;
            $flatArray = ['pos' => ' '];

            array_walk_recursive(
                $item['GeoObject'],

                /**
                 * @param string $value
                 */
                function ($value, $key) use (&$flatArray) {
                    $flatArray[$key] = $value;
                }
            );

            if (!empty($flatArray['lowerCorner']) && !empty($flatArray['upperCorner'])) {
                $lowerCorner = explode(' ', $flatArray['lowerCorner']);
                $upperCorner = explode(' ', $flatArray['upperCorner']);
                $builder->setBounds(
                    (float) $lowerCorner[1],
                    (float) $lowerCorner[0],
                    (float) $upperCorner[1],
                    (float) $upperCorner[0]
                );
            }

            $coordinates = explode(' ', $flatArray['pos']);
            $builder->setCoordinates((float) $coordinates[1], (float) $coordinates[0]);

            foreach (['AdministrativeAreaName', 'SubAdministrativeAreaName'] as $i => $name) {
                if (isset($flatArray[$name])) {
                    $builder->addAdminLevel($i + 1, $flatArray[$name], null);
                }
            }

            $builder->setStreetNumber(isset($flatArray['PremiseNumber']) ? $flatArray['PremiseNumber'] : null);
            $builder->setStreetName(isset($flatArray['ThoroughfareName']) ? $flatArray['ThoroughfareName'] : null);
            $builder->setSubLocality(isset($flatArray['DependentLocalityName']) ? $flatArray['DependentLocalityName'] : null);
            $builder->setLocality(isset($flatArray['LocalityName']) ? $flatArray['LocalityName'] : null);
            $builder->setCountry(isset($flatArray['CountryName']) ? $flatArray['CountryName'] : null);
            $builder->setCountryCode(isset($flatArray['CountryNameCode']) ? $flatArray['CountryNameCode'] : null);

            /** @var YandexAddress $location */
            $location = $builder->build(YandexAddress::class);
            $location = $location->withPrecision(isset($flatArray['precision']) ? $flatArray['precision'] : null);
            $location = $location->withName(isset($flatArray['name']) ? $flatArray['name'] : null);
            $location = $location->withKind($flatArray['kind'] ?? null);
            $locations[] = $location;
        }

        return new AddressCollection($locations);
    }
}
