<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Ip2Geo;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\Address;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

final class Ip2Geo extends AbstractHttpProvider implements Provider
{
    private const BASE_URL = 'https://api.ip2geo.dev';

    private string $apiKey;

    public function __construct(ClientInterface $client, string $apiKey)
    {
        if ('' === $apiKey) {
            throw new InvalidCredentials('An API key is required.');
        }

        $this->apiKey = $apiKey;

        parent::__construct($client);
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The ip2geo provider does not support street addresses, only IP addresses.');
        }

        if (in_array($address, ['127.0.0.1', '::1', '0.0.0.0'], true)) {
            return new AddressCollection([Address::createFromArray([])]);
        }

        $url = sprintf('%s/convert?ip=%s', self::BASE_URL, $address);

        $request = $this->getRequest($url);
        $request = $request->withHeader('X-Api-Key', $this->apiKey);

        $content = $this->getParsedResponse($request);

        $json = json_decode($content, true);

        if (!is_array($json) || !isset($json['success'])) {
            throw new InvalidServerResponse(sprintf('Could not decode response from ip2geo for IP "%s".', $address));
        }

        if (true !== $json['success'] || !isset($json['data'])) {
            return new AddressCollection([]);
        }

        $data = $json['data'];

        return $this->buildResult($data);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The ip2geo provider is not able to do reverse geocoding.');
    }

    public function getName(): string
    {
        return 'ip2geo';
    }

    private function buildResult(array $data): AddressCollection
    {
        $builder = new AddressBuilder($this->getName());

        $continent = $data['continent'] ?? [];
        $country = $continent['country'] ?? [];
        $subdivision = $country['subdivision'] ?? [];
        $city = $country['city'] ?? [];
        $timezone = $city['timezone'] ?? [];
        $flag = $country['flag'] ?? [];
        $currency = $country['currency'] ?? [];
        $asn = $data['asn'] ?? [];
        $registeredCountry = $data['registered_country'] ?? [];

        // Standard geocoder fields
        if (isset($city['latitude'], $city['longitude'])) {
            $builder->setCoordinates((float) $city['latitude'], (float) $city['longitude']);
        }

        if (isset($city['name'])) {
            $builder->setLocality($city['name']);
        }

        if (isset($city['postal_code'])) {
            $builder->setPostalCode($city['postal_code']);
        }

        if (isset($subdivision['name'], $subdivision['code'])) {
            $builder->addAdminLevel(1, $subdivision['name'], $subdivision['code']);
        }

        if (isset($country['name'], $country['code'])) {
            $builder->setCountry($country['name']);
            $builder->setCountryCode($country['code']);
        }

        if (isset($timezone['name'])) {
            $builder->setTimezone($timezone['name']);
        }

        // Build custom address with extra ip2geo fields
        /** @var Ip2GeoAddress $address */
        $address = $builder->build(Ip2GeoAddress::class);

        $address = $address
            ->withIp($data['ip'] ?? null)
            ->withIpType($data['type'] ?? null)
            ->withIsEu($data['is_eu'] ?? null)
            ->withContinentName($continent['name'] ?? null)
            ->withContinentCode($continent['code'] ?? null)
            ->withPhoneCode($country['phone_code'] ?? null)
            ->withCapital($country['capital'] ?? null)
            ->withTld($country['tld'] ?? null)
            ->withFlagEmoji($flag['emoji'] ?? null)
            ->withFlagImg($flag['img'] ?? null)
            ->withCurrencyName($currency['name'] ?? null)
            ->withCurrencyCode($currency['code'] ?? null)
            ->withCurrencySymbol($currency['symbol'] ?? null)
            ->withGeonameId(isset($city['geoname_id']) ? (int) $city['geoname_id'] : null)
            ->withContinentGeonameId(isset($continent['geoname_id']) ? (int) $continent['geoname_id'] : null)
            ->withCountryGeonameId(isset($country['geoname_id']) ? (int) $country['geoname_id'] : null)
            ->withMetroCode(isset($city['metro_code']) ? (int) $city['metro_code'] : null)
            ->withFlagEmojiUnicode($flag['emoji_unicode'] ?? null)
            ->withAccuracyRadius(isset($city['accuracy_radius']) ? (int) $city['accuracy_radius'] : null)
            ->withTimeNow($timezone['time_now'] ?? null)
            ->withAsnNumber(isset($asn['number']) ? (int) $asn['number'] : null)
            ->withAsnName($asn['name'] ?? null)
            ->withRegisteredCountryName($registeredCountry['name'] ?? null)
            ->withRegisteredCountryCode($registeredCountry['code'] ?? null)
            ->withRegisteredCountryGeonameId(isset($registeredCountry['geoname_id']) ? (int) $registeredCountry['geoname_id'] : null);

        return new AddressCollection([$address]);
    }
}
