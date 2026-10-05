<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IP2LocationIo;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

/**
 * @author IP2Location <support@ip2location.com>
 */
final class IP2LocationIo extends AbstractHttpProvider implements Provider
{
	/**
	 * @var string
	 */
	public const ENDPOINT_URL = 'https://api.ip2location.io/?key=%s&ip=%s';

	/**
	 * @var string
	 */
	private $apiKey;

	/**
	 * @var string
	 */
	private $endpointUrl;

	/**
	 * @param ClientInterface $client an HTTP adapter
	 * @param string          $apiKey an API key
	 *
	 * @throws \Geocoder\Exception\InvalidArgument
	 */
	public function __construct(ClientInterface $client, string $apiKey)
	{
		parent::__construct($client);

		$this->apiKey = $apiKey;
	}

	public function geocodeQuery(GeocodeQuery $query): Collection
	{
		$address = $query->getText();
		if ($this->apiKey === null) {
			throw new InvalidCredentials('No API Key provided.');
		}

		if (!filter_var($address, FILTER_VALIDATE_IP)) {
			throw new UnsupportedOperation('The IP2LocationIo provider does not support street addresses, only IPv4 or IPv6 addresses.');
		}

		if ($address === '127.0.0.1') {
			return new AddressCollection([$this->getLocationForLocalhost()]);
		}

		$url = sprintf(self::ENDPOINT_URL, $this->apiKey, $address);

		return $this->executeQuery($url);
	}

	public function reverseQuery(ReverseQuery $query): Collection
	{
		throw new UnsupportedOperation('The IP2LocationIo provider is not able to do reverse geocoding.');
	}

	public function getName(): string
	{
		return 'ip2location_io';
	}

	private function executeQuery(string $url): AddressCollection
	{
		$content = $this->getUrlContents($url);
		$data = json_decode($content, true);

		if (empty($data) || isset($data['error'])) {
			return new AddressCollection([]);
		}

		$timeZone = timezone_name_from_abbr('', (int) substr($data['time_zone'], 0, strpos($data['time_zone'], ':')) * 3600, 0);

		if (isset($data['time_zone_info']['olson'])) {
			$timeZone = $data['time_zone_info']['olson'];
		}

		return new AddressCollection([
			Address::createFromArray([
				'providedBy'  => $this->getName(),
				'latitude'    => $data['latitude'] ?? null,
				'longitude'   => $data['longitude'] ?? null,
				'locality'    => $data['city_name'] ?? null,
				'postalCode'  => $data['zip_code'] ?? null,
				'adminLevels' => [['name' => $data['region_name'], 'level' => 1]],
				'country'     => $data['country_name'] ?? null,
				'countryCode' => $data['country_code'] ?? null,
				'timezone'    => $timeZone,
			]),
		]);
	}
}
