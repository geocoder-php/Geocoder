<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Geonames;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Provider\Geonames\Model\CountryInfo;
use Geocoder\Provider\Geonames\Model\GeonamesAddress;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

/**
 * @author Giovanni Pirrotta <giovanni.pirrotta@gmail.com>
 */
final class Geonames extends AbstractHttpProvider implements Provider
{

    /**
     * @var string
     */
    public const PREMIUM_WEBSERVICE_BASE_URL = 'https://secure.geonames.net';

    /**
     * @var string
     */
    public const FREE_WEBSERVICE_BASE_URL = 'http://api.geonames.org';

    /**
     * @var string
     */
    public const GEOCODE_ENDPOINT_PATH = '%s/searchJSON?q=%s&maxRows=%d&style=full&username=%s';

    /**
     * @var string
     */
    public const REVERSE_ENDPOINT_PATH = '%s/findNearbyPlaceNameJSON?lat=%F&lng=%F&style=full&maxRows=%d&username=%s';

    /**
     * @var string
     */
    private $username;

    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param ClientInterface $client   An HTTP adapter
     * @param string          $username Username login (Free registration at http://www.geonames.org/login)
     * @param string|null     $token    Optional token for premium accounts
     * @param bool            $secure   Use secure endpoint (https://secure.geonames.net) for premium accounts
     */
    public function __construct(ClientInterface $client, string $username, ?string $token = null, bool $secure = false)
    {
        if (empty($username)) {
            throw new InvalidCredentials('No username provided.');
        }

        $this->username = $username;
        $this->token = $token;

        // Determine base URL based on secure flag.
        if ($secure === true) {
            $this->baseUrl = self::PREMIUM_WEBSERVICE_BASE_URL;
        } else {
            $this->baseUrl = self::FREE_WEBSERVICE_BASE_URL;
        }

        parent::__construct($client);
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Geonames provider does not support IP addresses.');
        }

        $url = sprintf(
            self::GEOCODE_ENDPOINT_PATH,
            $this->baseUrl,
            urlencode($address),
            $query->getLimit(),
            $this->username
        );

        $url = $this->appendToken($url);

        return $this->executeQuery($url, $query->getLocale());
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();

        $url = sprintf(
            self::REVERSE_ENDPOINT_PATH,
            $this->baseUrl,
            $latitude,
            $longitude,
            $query->getLimit(),
            $this->username
        );

        $url = $this->appendToken($url);

        return $this->executeQuery($url, $query->getLocale());
    }

    /**
     * @return CountryInfo[]
     */
    public function getCountryInfo(?string $country = null, ?string $locale = null): array
    {
        $url = sprintf('%s/countryInfoJSON?username=%s', $this->baseUrl, $this->username);

        if (isset($country)) {
            $url = sprintf('%s&country=%s', $url, $country);
        }

        $url = sprintf('%s&style=FULL', $url);

        if (null !== $locale) {
            // Locale code transformation: for example from it_IT to it
            $url = sprintf('%s&lang=%s', $url, substr($locale, 0, 2));
        }

        $url = $this->appendToken($url);

        $content = $this->getUrlContents($url);
        if (null === $json = json_decode($content)) {
            throw InvalidServerResponse::create($url);
        }

        if (!isset($json->geonames)) {
            return [];
        }

        $data = $json->geonames;

        if (empty($data)) {
            return [];
        }

        $results = [];

        foreach ($data as $item) {
            $countryInfo = new CountryInfo();

            $results[] = $countryInfo
                ->setBounds($item->south, $item->west, $item->north, $item->east)
                ->withContinent($item->continent ?? null)
                ->withCapital($item->capital ?? null)
                ->withLanguages($item->langesuages ?? '')
                ->withGeonameId($item->geonameId ?? null)
                ->withIsoAlpha3($item->isoAlpha3 ?? null)
                ->withFipsCode($item->fipsCode ?? null)
                ->withPopulation($item->population ?? null)
                ->withIsoNumeric($item->isoNumeric ?? null)
                ->withAreaInSqKm($item->areaInSqKm ?? null)
                ->withCountryCode($item->countryCode ?? null)
                ->withCountryName($item->countryName ?? null)
                ->withContinentName($item->continentName ?? null)
                ->withCurrencyCode($item->currencyCode ?? null);
        }

        return $results;
    }

    public function getName(): string
    {
        return 'geonames';
    }

    private function executeQuery(string $url, ?string $locale = null): AddressCollection
    {
        if (null !== $locale) {
            // Locale code transformation: for example from it_IT to it
            $url = sprintf('%s&lang=%s', $url, substr($locale, 0, 2));
        }

        $content = $this->getUrlContents($url);
        if (null === $json = json_decode($content)) {
            throw InvalidServerResponse::create($url);
        }

        if (isset($json->totalResultsCount) && empty($json->totalResultsCount)) {
            return new AddressCollection([]);
        }

        if (!isset($json->geonames)) {
            return new AddressCollection([]);
        }

        $data = $json->geonames;

        if (empty($data)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($data as $item) {
            $builder = new AddressBuilder($this->getName());

            if (isset($item->bbox)) {
                $builder->setBounds($item->bbox->south, $item->bbox->west, $item->bbox->north, $item->bbox->east);
            }

            for ($level = 1; $level <= AdminLevelCollection::MAX_LEVEL_DEPTH; ++$level) {
                $adminNameProp = 'adminName'.$level;
                $adminCodeProp = 'adminCode'.$level;
                if (!empty($item->$adminNameProp)) {
                    $builder->addAdminLevel($level, $item->$adminNameProp, $item->$adminCodeProp ?? null);
                }
            }

            $builder->setCoordinates($item->lat ?? null, $item->lng ?? null);
            $builder->setLocality($item->name ?? null);
            $builder->setCountry($item->countryName ?? null);
            $builder->setCountryCode($item->countryCode ?? null);
            $builder->setTimezone($item->timezone->timeZoneId ?? null);

            /** @var GeonamesAddress $address */
            $address = $builder->build(GeonamesAddress::class);
            $address = $address->withName($item->name ?? null);
            $address = $address->withAsciiName($item->asciiName ?? null);
            $address = $address->withFclName($item->fclName ?? null);
            $address = $address->withAlternateNames($item->alternateNames ?? []);
            $address = $address->withPopulation($item->population ?? null);
            $address = $address->withGeonameId($item->geonameId ?? null);
            $address = $address->withFcode($item->fcode ?? null);

            $results[] = $address;
        }

        return new AddressCollection($results);
    }

    /**
     * Append token parameter to URL if token is provided.
     */
    private function appendToken(string $url): string
    {
        if (null !== $this->token && '' !== $this->token) {
            $url = sprintf('%s&token=%s', $url, $this->token);
        }

        return $url;
    }
}
