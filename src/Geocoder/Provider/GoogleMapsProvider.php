<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Provider\ProviderInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GoogleMapsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param string $apiKey
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey)
    {
        parent::__construct($adapter, null);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (null === $this->apiKey) {
            throw new \RuntimeException('No API Key provided');
        }

        if ('127.0.0.1' === $address) {
            return array(
                'city'      => 'localhost',
                'region'    => 'localhost',
                'country'   => 'localhost'
            );
        }

        $query = sprintf('http://maps.google.com/maps/geo?q=%s&output=json&key=%s', urlencode($address), $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        return $this->getGeocodedData(sprintf('%s,%s', $coordinates[0], $coordinates[1]));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'google_maps';
    }

    /**
     * @param string $query
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $json = json_decode($content);

        if (isset($json->Placemark)) {
            $data = (array)json_decode($content)->Placemark[0];
        } else {
            return $this->getDefaults();
        }

        $coordinates = (array) $data['Point']->coordinates;
        $locality    = $data['AddressDetails']->Country->AdministrativeArea->SubAdministrativeArea->Locality;

        $zipcode = null;

        if (isset($locality->PostalCode)) {
            $zipcode = (string) $locality->PostalCode->PostalCodeNumber;
        } elseif (isset($locality->DependentLocality->PostalCode->PostalCodeNumber)) {
            $zipcode = (string) $locality->DependentLocality->PostalCode->PostalCodeNumber;
        }

        $city = (string) $data['AddressDetails']->Country->AdministrativeArea->SubAdministrativeArea->Locality->LocalityName;
        $region = (string) $data['AddressDetails']->Country->AdministrativeArea->AdministrativeAreaName;
        $country = (string) $data['AddressDetails']->Country->CountryName;

        return array(
            'latitude'  => $coordinates[1],
            'longitude' => $coordinates[0],
            'city'      => empty($city) ? null : $city,
            'zipcode'   => empty($zipcode) ? null : $zipcode,
            'region'    => empty($region) ? null : $region,
            'country'   => empty($country) ? null : $country
        );
    }
}
