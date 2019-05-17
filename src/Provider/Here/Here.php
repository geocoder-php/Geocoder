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
use Geocoder\Exception\InvalidCredentials;
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
    const GEOCODE_ENDPOINT_URL = 'https://geocoder.api.here.com/6.2/geocode.json?app_id=%s&app_code=%s&searchtext=%s&gen=8';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://reverse.geocoder.api.here.com/6.2/reversegeocode.json?prox=%F,%F&250&app_id=%s&app_code=%s&mode=retrieveAddresses&gen=8&maxresults=%d';

    /**
     * @var string
     */
    const GEOCODE_CIT_ENDPOINT_URL = 'https://geocoder.cit.api.here.com/6.2/geocode.json?app_id=%s&app_code=%s&searchtext=%s&gen=8';

    /**
     * @var string
     */
    const REVERSE_CIT_ENDPOINT_URL = 'https://reverse.geocoder.cit.api.here.com/6.2/reversegeocode.json?prox=%F,%F&250&app_id=%s&app_code=%s&mode=retrieveAddresses&gen=8&maxresults=%d';

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

        if (null !== $query->getLocale()) {
            $url = sprintf('%s&language=%s', $url, $query->getLocale());
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
     * @return \Geocoder\Collection
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
            $builder->setCountry($location['Address']['AdditionalData'][0]['value'] ?? null);
            $builder->setCountryCode($location['Address']['Country'] ?? null);

            $address = $builder->build(HereAddress::class);
            $address = $address->withLocationId($location['LocationId']);
            $address = $address->withLocationType($location['LocationType']);
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
}
