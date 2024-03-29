<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenRouteService;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Provider\Pelias\Pelias;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

final class OpenRouteService extends Pelias implements Provider
{
    public const API_URL = 'https://api.openrouteservice.org/geocode';

    public const API_VERSION = 1;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param ClientInterface $client an HTTP adapter
     * @param string          $apiKey an API key
     */
    public function __construct(ClientInterface $client, string $apiKey)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
        parent::__construct($client, self::API_URL, self::API_VERSION);

        /*
         * Openrouteservice does not use /v1 in first version, but plan to add
         *  /v2 in next version.
         *
         * @see https://ask.openrouteservice.org/t/pelias-version-in-api-url/1021
         */
        if (self::API_VERSION === 1) {
            $this->root = self::API_URL;
        }
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $url = $this->getGeocodeQueryUrl($query, [
            'api_key' => $this->apiKey,
        ]);

        return $this->executeQuery($url);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $url = $this->getReverseQueryUrl($query, [
            'api_key' => $this->apiKey,
        ]);

        return $this->executeQuery($url);
    }

    public function getName(): string
    {
        return 'openrouteservice';
    }
}
