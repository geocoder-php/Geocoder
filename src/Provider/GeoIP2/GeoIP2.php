<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GeoIP2;

use Geocoder\Collection;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractProvider;
use Geocoder\Provider\Provider;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Exception\AuthenticationException;
use GeoIp2\Exception\OutOfQueriesException;

/**
 * @author Jens Wiese <jens@howtrueisfalse.de>
 */
final class GeoIP2 extends AbstractProvider implements Provider
{
    /**
     * @var GeoIP2Adapter
     */
    private $adapter;

    public function __construct(GeoIP2Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        $locale = $query->getLocale() ?: 'en'; // Default to English
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GeoIP2 provider does not support street addresses, only IP addresses.');
        }

        if ('127.0.0.1' === $address) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        $result = json_decode($this->executeQuery($address));

        if (null === $result) {
            return new AddressCollection([]);
        }

        $adminLevels = [];
        if (isset($result->subdivisions) && is_array($result->subdivisions)) {
            foreach ($result->subdivisions as $i => $subdivision) {
                $name = (isset($subdivision->names->{$locale}) ? $subdivision->names->{$locale} : null);
                $code = (isset($subdivision->iso_code) ? $subdivision->iso_code : null);

                if (null !== $name || null !== $code) {
                    $adminLevels[] = ['name' => $name, 'code' => $code, 'level' => $i + 1];
                }
            }
        }

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'countryCode' => (isset($result->country->iso_code) ? $result->country->iso_code : null),
                'country' => (isset($result->country->names->{$locale}) ? $result->country->names->{$locale} : null),
                'locality' => (isset($result->city->names->{$locale}) ? $result->city->names->{$locale} : null),
                'latitude' => (isset($result->location->latitude) ? $result->location->latitude : null),
                'longitude' => (isset($result->location->longitude) ? $result->location->longitude : null),
                'timezone' => (isset($result->location->time_zone) ? $result->location->time_zone : null),
                'postalCode' => (isset($result->postal->code) ? $result->postal->code : null),
                'adminLevels' => $adminLevels,
            ]),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The GeoIP2 provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'geoip2';
    }

    /**
     * @param string $address
     */
    private function executeQuery(string $address): string
    {
        $uri = sprintf('file://geoip?%s', $address);

        try {
            $result = $this->adapter->getContent($uri);
        } catch (AddressNotFoundException $e) {
            return '';
        } catch (AuthenticationException $e) {
            throw new InvalidCredentials($e->getMessage(), $e->getCode(), $e);
        } catch (OutOfQueriesException $e) {
            throw new QuotaExceeded($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }
}
