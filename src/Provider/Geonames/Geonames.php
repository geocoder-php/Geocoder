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
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Provider\Geonames\Model\CountryInfo;
use Geocoder\Provider\Geonames\Model\GeonamesAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Giovanni Pirrotta <giovanni.pirrotta@gmail.com>
 */
final class Geonames extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://api.geonames.org/searchJSON?q=%s&maxRows=%d&style=full&username=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://api.geonames.org/findNearbyPlaceNameJSON?lat=%F&lng=%F&style=full&maxRows=%d&username=%s';

    /**
     * @var string
     */
    const BASE_ENDPOINT_URL = 'http://api.geonames.org/%s?username=%s';

    /**
     * @var string
     */
    private $username;

    /**
     * @param HttpClient $client   An HTTP adapter
     * @param string     $username Username login (Free registration at http://www.geonames.org/login)
     */
    public function __construct(HttpClient $client, string $username)
    {
        if (empty($username)) {
            throw new InvalidCredentials('No username provided.');
        }

        $this->username = $username;
        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Geonames provider does not support IP addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $query->getLimit(), $this->username);

        return $this->executeQuery($url, $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();

        $url = sprintf(self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $query->getLimit(), $this->username);

        return $this->executeQuery($url, $query->getLocale());
    }

    /**
     * @param string|null $country
     * @param string|null $locale
     *
     * @return array
     *
     * @throws \Geocoder\Exception\Exception
     */
    public function getCountryInfo(?string $country = null, ?string $locale = null): array
    {
        $url = sprintf(self::BASE_ENDPOINT_URL, 'countryInfoJSON', $this->username);

        if (isset($country)) {
            $url = sprintf('%s&country=%s', $url, $country);
        }

        $url = sprintf('%s&style=FULL', $url);

        if (null !== $locale) {
            // Locale code transformation: for example from it_IT to it
            $url = sprintf('%s&lang=%s', $url, substr($locale, 0, 2));
        }

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

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'geonames';
    }

    /**
     * @param string      $url
     * @param string|null $locale
     *
     * @return AddressCollection
     */
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
}
