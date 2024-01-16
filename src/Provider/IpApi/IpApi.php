<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpApi;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\IpApi\Model\IpApiLocation;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

final class IpApi extends AbstractHttpProvider
{
    private const URL = '{host_prefix}ip-api.com/json/{ip}';

    private const FIELDS = 'status,message,lat,lon,city,district,zip,country,countryCode,timezone,regionName,region,proxy,hosting';

    private string|null $apiKey;

    public function __construct(ClientInterface $client, string $apiKey = null)
    {
        $this->apiKey = $apiKey;
        parent::__construct($client);
    }

    #[\Override]
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $ip = $query->getText();

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The ip-api provider does not support street addresses.');
        }

        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        $url = $this->buildUrl($ip, $query->getLocale());

        $body = $this->getUrlContents($url);

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if ('fail' === $data['status']) {
            $this->throwError($data['message']);
        }

        $location = $this->buildLocation($data);

        return new AddressCollection([$location]);
    }

    #[\Override]
    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The ip-api provider is not able to do reverse geocoding.');
    }

    #[\Override]
    public function getName(): string
    {
        return 'ip-api';
    }

    public function buildUrl(string $ip, string|null $locale): string
    {
        $baseUrl = strtr(self::URL, [
            '{host_prefix}' => $this->apiKey ? 'https://pro.' : 'http://',
            '{ip}' => $ip,
        ]);

        $query = http_build_query(array_filter([
            'key' => $this->apiKey,
            'lang' => $locale,
            'fields' => self::FIELDS,
        ]));

        return $baseUrl.'?'.$query;
    }

    /**
     * @param array<string, scalar> $data
     */
    private function buildLocation(array $data): IpApiLocation
    {
        $data = array_map(
            static fn ($value) => '' === $value ? null : $value,
            $data,
        );

        $builder = new AddressBuilder($this->getName());
        $builder->setCoordinates($data['lat'], $data['lon']);
        $builder->setLocality($data['city']);
        $builder->setSubLocality($data['district']);
        $builder->setPostalCode($data['zip']);
        $builder->setCountry($data['country']);
        $builder->setCountryCode($data['countryCode']);
        $builder->setTimezone($data['timezone']);

        if ($data['regionName']) {
            $builder->addAdminLevel(1, $data['regionName'], $data['region']);
        }

        /** @var IpApiLocation $location */
        $location = $builder->build(IpApiLocation::class);

        return $location
            ->withIsProxy($data['proxy'])
            ->withIsHosting($data['hosting']);
    }

    /**
     * @see https://members.ip-api.com/faq#errors
     *
     * @return never
     */
    private function throwError(string $message)
    {
        if (
            in_array($message, ['private range', 'reserved range', 'invalid query'], true)
            || str_contains('Origin restriction', $message)
            || str_contains('IP range restriction', $message)
            || str_contains('Calling IP restriction', $message)
        ) {
            throw new InvalidArgument($message);
        }

        if (
            str_contains('invalid/expired ke', $message)
            || str_contains('no API key supplied', $message)
        ) {
            throw new InvalidCredentials($message);
        }

        throw new InvalidServerResponse($message);
    }
}
