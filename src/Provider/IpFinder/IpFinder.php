<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpFinder;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Collection;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class IpFinder extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'https://api.ipfinder.io/v1/%s?token=%s';

    /**
     *  @var string
     */
    const DEFAULT_API_TOKEN = 'free';

    /**
     * @var string
     */
    public $apiKey;

    /**
     * @param HttpClient  $client an HTTP adapter
     * @param string|null $apiKey an API key
     */
    public function __construct(HttpClient $client, string $apiKey = null)
    {
        if (isset($apiKey)) {
            $this->apiKey = $apiKey;
        } else {
            $this->apiKey = self::DEFAULT_API_TOKEN;
        }

        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The IpFinder provider support only IP addresses.');
        }

        if (in_array($address, ['127.0.0.1', '::1'], true)) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        return $this->executeQuery(sprintf(self::ENDPOINT_URL, $address, $this->apiKey));
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The IpFinder provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ipfinder';
    }

    /**
     * @param string $url
     *
     * @return Collection
     */
    private function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $data = json_decode($content, true);

        if (isset($data['errors'])) {
            switch ($data['errors'][0]['code']) {
                case 104:
                    throw new QuotaExceeded(
                        'You have reached your usage limit. Upgrade your plan if necessary.'
                    );
                case 401:
                    throw new InvalidArgument(
                        'No API Key was specified, invalid API Key.'
                    );
                case 404:
                    throw new InvalidCredentials(
                        'The requested resource does not exist.'
                    );
                case 402:
                    throw new QuotaExceeded(
                        'Expired payment method, please upgrade your plan.'
                    );
                case 105:
                    throw new InvalidCredentials(
                        'Non payment: Please login to your dashboard and pay the invoice.'
                    );
            }
        }

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'locality' => $data['city'] ?: null,
                'countryCode' => $data['country_code'] ?: null,
                'country' => $data['country_name'] ?: null,
            ]),
        ]);
    }
}
