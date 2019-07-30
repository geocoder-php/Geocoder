<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Here;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Geocoder\Provider\Here\Model\HereAddress;
use Http\Client\HttpClient;

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */
final class Here extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://geocoder.api.here.com/6.2/geocode.json?app_id=%s&app_code=%s&searchtext=%s&gen=9';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://reverse.geocoder.api.here.com/6.2/reversegeocode.json?prox=%F,%F,250&app_id=%s&app_code=%s&mode=retrieveAddresses&gen=9&maxresults=%d';

    /**
     * @var string
     */
    const GEOCODE_CIT_ENDPOINT_URL = 'https://geocoder.cit.api.here.com/6.2/geocode.json?app_id=%s&app_code=%s&searchtext=%s&gen=9';

    /**
     * @var string
     */
    const REVERSE_CIT_ENDPOINT_URL = 'https://reverse.geocoder.cit.api.here.com/6.2/reversegeocode.json?prox=%F,%F,250&app_id=%s&app_code=%s&mode=retrieveAddresses&gen=9&maxresults=%d';

    /**
     * @var string
     */
    private $appId;

    /**
     * @var string
     */
    private $appCode;

    /**
     * @var bool
     */
    private $useCIT;

    /**
     * @param HttpClient $client  An HTTP adapter.
     * @param string     $appId   An App ID.
     * @param string     $appCode An App code.
     * @param bool       $useCIT  Use Customer Integration Testing environment (CIT) instead of production.
     */
    public function __construct(HttpClient $client, string $appId, string $appCode, bool $useCIT = false)
    {
        if (empty($appId) || empty($appCode)) {
            throw new InvalidCredentials('Invalid or missing api key.');
        }
        $this->appId = $appId;
        $this->appCode = $appCode;
        $this->useCIT = $useCIT;

        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        // This API doesn't handle IPs
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Here provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf($this->useCIT ? self::GEOCODE_CIT_ENDPOINT_URL : self::GEOCODE_ENDPOINT_URL, $this->appId, $this->appCode, rawurlencode($query->getText()));

        if (null !== $query->getData('country')) {
            $url = sprintf('%s&country=%s', $url, rawurlencode($query->getData('country')));
        }

        if (null !== $query->getData('state')) {
            $url = sprintf('%s&state=%s', $url, rawurlencode($query->getData('state')));
        }

        if (null !== $query->getData('county')) {
            $url = sprintf('%s&county=%s', $url, rawurlencode($query->getData('county')));
        }

        if (null !== $query->getData('city')) {
            $url = sprintf('%s&city=%s', $url, rawurlencode($query->getData('city')));
        }

        if (null !== $query->getLocale()) {
            $url = sprintf('%s&language=%s', $url, $query->getLocale());
        }

        $additionalDataParam = [];
        if (null !== $query->getData('CrossingStreets')) {
            $additionalDataParam['CrossingStreets'] = $query->getData('CrossingStreets');
        }

        if (null !== $query->getData('PreserveUnitDesignators')) {
            $additionalDataParam['PreserveUnitDesignators'] = $query->getData('PreserveUnitDesignators');
        }

        if (null !== $query->getData('Country2')) {
            $additionalDataParam['Country2'] = $query->getData('Country2');
        }

        if (null !== $query->getData('IncludeChildPOIs')) {
            $additionalDataParam['IncludeChildPOIs'] = $query->getData('IncludeChildPOIs');
        }

        if (null !== $query->getData('IncludeRoutingInformation')) {
            $additionalDataParam['IncludeRoutingInformation'] = $query->getData('IncludeRoutingInformation');
        }

        if (null !== $query->getData('AdditionalAddressProvider')) {
            $additionalDataParam['AdditionalAddressProvider'] = $query->getData('AdditionalAddressProvider');
        }

        if (null !== $query->getData('HouseNumberMode')) {
            $additionalDataParam['HouseNumberMode'] = $query->getData('HouseNumberMode');
        }

        if (null !== $query->getData('FlexibleAdminValues')) {
            $additionalDataParam['FlexibleAdminValues'] = $query->getData('FlexibleAdminValues');
        }

        if (null !== $query->getData('IntersectionSnapTolerance')) {
            $additionalDataParam['IntersectionSnapTolerance'] = $query->getData('IntersectionSnapTolerance');
        }

        if (null !== $query->getData('AddressRangeSqueezeOffset')) {
            $additionalDataParam['AddressRangeSqueezeOffset'] = $query->getData('AddressRangeSqueezeOffset');
        }

        if (null !== $query->getData('AddressRangeSqueezeFactor')) {
            $additionalDataParam['AddressRangeSqueezeFactor'] = $query->getData('AddressRangeSqueezeFactor');
        }

        if (null !== $query->getData('IncludeShapeLevel')) {
            $additionalDataParam['IncludeShapeLevel'] = $query->getData('IncludeShapeLevel');
        }

        if (null !== $query->getData('RestrictLevel')) {
            $additionalDataParam['RestrictLevel'] = $query->getData('RestrictLevel');
        }

        if (null !== $query->getData('SuppressStreetType')) {
            $additionalDataParam['SuppressStreetType'] = $query->getData('SuppressStreetType');
        }

        if (null !== $query->getData('NormalizeNames')) {
            $additionalDataParam['NormalizeNames'] = $query->getData('NormalizeNames');
        }

        if (null !== $query->getData('IncludeMicroPointAddresses')) {
            $additionalDataParam['IncludeMicroPointAddresses'] = $query->getData('IncludeMicroPointAddresses');
        }

        $additionalDataParam['IncludeShapeLevel'] = 'country';

        if (!empty($additionalDataParam)) {
            $url = sprintf('%s&additionaldata=%s', $url, $this->serializeComponents($additionalDataParam));
        }

        return $this->executeQuery($url, $query->getLimit());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $url = sprintf($this->useCIT ? self::REVERSE_CIT_ENDPOINT_URL : self::REVERSE_ENDPOINT_URL, $coordinates->getLatitude(), $coordinates->getLongitude(), $this->appId, $this->appCode, $query->getLimit());

        return $this->executeQuery($url, $query->getLimit());
    }

    /**
     * @param string $url
     * @param int    $limit
     *
     * @return Collection
     */
    private function executeQuery(string $url, int $limit): Collection
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);

        if (isset($json['type'])) {
            switch ($json['type']['subtype']) {
                case 'InvalidInputData':
                    throw new InvalidArgument('Input parameter validation failed.');
                case 'QuotaExceeded':
                    throw new QuotaExceeded('Valid request but quota exceeded.');
                case 'InvalidCredentials':
                    throw new InvalidCredentials('Invalid or missing api key.');
            }
        }

        if (!isset($json['Response']) || empty($json['Response'])) {
            return new AddressCollection([]);
        }

        if (!isset($json['Response']['View'][0])) {
            return new AddressCollection([]);
        }

        $locations = $json['Response']['View'][0]['Result'];

        foreach ($locations as $loc) {
            $location = $loc['Location'];
            $builder = new AddressBuilder($this->getName());
            $coordinates = isset($location['NavigationPosition'][0]) ? $location['NavigationPosition'][0] : $location['DisplayPosition'];
            $builder->setCoordinates($coordinates['Latitude'], $coordinates['Longitude']);
            $bounds = $location['MapView'];

            $builder->setBounds($bounds['BottomRight']['Latitude'], $bounds['TopLeft']['Longitude'], $bounds['TopLeft']['Latitude'], $bounds['BottomRight']['Longitude']);
            $builder->setStreetNumber($location['Address']['HouseNumber'] ?? null);
            $builder->setStreetName($location['Address']['Street'] ?? null);
            $builder->setPostalCode($location['Address']['PostalCode'] ?? null);
            $builder->setLocality($location['Address']['City'] ?? null);
            $builder->setSubLocality($location['Address']['District'] ?? null);
            $builder->setCountryCode($location['Address']['Country'] ?? null);

            // The name of the country can be found in the AdditionalData.
            $additionalData = $location['Address']['AdditionalData'] ?? null;
            if (!empty($additionalData)) {
                $builder->setCountry($additionalData[array_search('CountryName', array_column($additionalData, 'key'))]['value'] ?? null);
            }

            // There may be a second AdditionalData. For example if "IncludeRoutingInformation" parameter is added
            $extraAdditionalData = $loc['AdditionalData'] ?? [];

            /** @var HereAddress $address */
            $address = $builder->build(HereAddress::class);
            $address = $address->withLocationId($location['LocationId']);
            $address = $address->withLocationType($location['LocationType']);
            $address = $address->withAdditionalData(array_merge($additionalData, $extraAdditionalData));
            $address = $address->withShape($location['Shape'] ?? null);
            $results[] = $address;

            if (count($results) >= $limit) {
                break;
            }
        }

        return new AddressCollection($results);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Here';
    }

    /**
     * Serialize the component query parameter.
     *
     * @param array $components
     *
     * @return string
     */
    private function serializeComponents(array $components): string
    {
        return implode(';', array_map(function ($name, $value) {
            return sprintf('%s,%s', $name, $value);
        }, array_keys($components), $components));
    }
}
