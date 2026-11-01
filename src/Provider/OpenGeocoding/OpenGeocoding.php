<?php

declare(strict_types=1);

/*
 * This file is part of the OpenGeocoding provider for Geocoder PHP.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenGeocoding;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\OpenGeocoding\Model\OpenGeocodingAddress;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

/**
 * @see https://opengeocoding.com/docs
 */
final class OpenGeocoding extends AbstractHttpProvider implements Provider
{
    public const API_URL = 'https://api.opengeocoding.com';

    private const MAX_REVERSE_RESULTS = 10;

    private string $apiKey;

    public function __construct(ClientInterface $client, string $apiKey)
    {
        if ('' === $apiKey) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
        parent::__construct($client);
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The OpenGeocoding provider does not support IP addresses, only street addresses.');
        }

        $json = $this->executeQuery(self::API_URL.'/v1/search?'.http_build_query(['addressString' => $address]));

        if ([] === $json) {
            return new AddressCollection([]);
        }

        return new AddressCollection([$this->buildAddress($json)]);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();

        $url = self::API_URL.'/v1/reverse?'.http_build_query([
            'lat' => $coordinates->getLatitude(),
            'lon' => $coordinates->getLongitude(),
            'size' => max(1, min(self::MAX_REVERSE_RESULTS, $query->getLimit())),
            'radiusM' => $query->getData('radiusM'),
        ]);

        $results = [];
        foreach ($this->executeQuery($url)['results'] ?? [] as $result) {
            $results[] = $this->buildAddress(
                $result['address'] ?? [],
                isset($result['distanceM']) ? (float) $result['distanceM'] : null,
                $result['layer'] ?? null
            );
        }

        return new AddressCollection($results);
    }

    public function getName(): string
    {
        return 'opengeocoding';
    }

    /**
     * @return array<string, mixed>
     */
    private function executeQuery(string $url): array
    {
        $request = $this->createRequest('GET', $url, [
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey,
        ]);
        $response = $this->getHttpClient()->sendRequest($request);

        $status = $response->getStatusCode();
        $json = json_decode((string) $response->getBody(), true);
        $message = is_array($json) && isset($json['message']) && is_string($json['message']) ? $json['message'] : null;

        if (401 === $status || 403 === $status) {
            throw new InvalidCredentials($message ?? 'Invalid or missing API key.');
        }
        if (429 === $status) {
            throw new QuotaExceeded($message ?? 'Rate limit or request credits exceeded.');
        }
        if (400 === $status && null !== $message) {
            throw new InvalidArgument($message);
        }
        if (200 !== $status || !is_array($json)) {
            throw InvalidServerResponse::create($url, $status);
        }

        return $json;
    }

    /**
     * @param array<string, mixed> $item a FullAddress object
     */
    private function buildAddress(array $item, ?float $distance = null, ?string $layer = null): OpenGeocodingAddress
    {
        $coordinates = $item['geoCoordinates'] ?? [];
        $details = $item['addressDetails'] ?? [];

        $builder = new AddressBuilder($this->getName());
        $builder->setCoordinates((float) ($coordinates['lat'] ?? 0), (float) ($coordinates['lon'] ?? 0));
        if (isset($coordinates['boundingBox'])) {
            $box = $coordinates['boundingBox'];
            $builder->setBounds(
                (float) ($box['minLat'] ?? 0),
                (float) ($box['minLon'] ?? 0),
                (float) ($box['maxLat'] ?? 0),
                (float) ($box['maxLon'] ?? 0)
            );
        }

        $builder->setStreetNumber($details['houseNumber'] ?? null);
        $builder->setStreetName($details['road'] ?? null);
        $builder->setPostalCode($details['postcode'] ?? null);
        $builder->setLocality($details['city'] ?? $details['town'] ?? $details['village'] ?? $details['hamlet'] ?? $details['municipality'] ?? null);
        $builder->setSubLocality($details['suburb'] ?? $details['borough'] ?? $details['cityDistrict'] ?? $details['neighbourhood'] ?? null);
        $builder->setCountry($details['country'] ?? null);
        $builder->setCountryCode(isset($details['countryCode']) ? strtoupper($details['countryCode']) : null);
        $this->addAdminLevels($builder, $details);

        /** @var OpenGeocodingAddress $address */
        $address = $builder->build(OpenGeocodingAddress::class);

        return $address
            ->withDisplayName($item['displayName'] ?? null)
            ->withGoodMatch((bool) ($item['goodMatch'] ?? false))
            ->withDistance($distance)
            ->withLayer($layer);
    }

    /**
     * @param array<string, mixed> $details an AddressDetails object
     */
    private function addAdminLevels(AddressBuilder $builder, array $details): void
    {
        $regions = array_values(array_filter(
            $details['regions'] ?? [],
            fn ($region) => isset($region['enName']) || isset($region['localName'])
        ));
        usort($regions, fn ($a, $b) => ($a['adminLevel'] ?? 0) <=> ($b['adminLevel'] ?? 0));

        // Geocoder's AdminLevelCollection only accepts levels 1 to 5.
        foreach (array_slice($regions, 0, 5) as $i => $region) {
            $builder->addAdminLevel($i + 1, $region['enName'] ?? $region['localName'], $region['adminCode'] ?? null);
        }

        if ([] !== $regions) {
            return;
        }

        $level = 1;
        foreach (['state', 'county'] as $field) {
            if (isset($details[$field]) && '' !== $details[$field]) {
                $builder->addAdminLevel($level++, $details[$field]);
            }
        }
    }
}
