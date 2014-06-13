<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResultException;
use Geocoder\Exception\InvalidArgumentException;
use Geocoder\Exception\RuntimeException;
use Geocoder\Exception\UnsupportedException;

class MaxMindBinaryProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    protected $datFile;

    /**
     * @var int|null
     */
    protected $openFlag;

    /**
     * @param string   $datFile
     * @param int|null $openFlag
     *
     * @throws RuntimeException         If maxmind's lib not installed.
     * @throws InvalidArgumentException If dat file is not correct (optional).
     */
    public function __construct($datFile, $openFlag = null)
    {
        if (false === function_exists('geoip_open')) {
            throw new RuntimeException('The MaxMindBinaryProvider requires maxmind\'s lib to be installed and loaded. Have you included geoip.inc file?');
        }

        if (false === function_exists('GeoIP_record_by_addr')) {
            throw new RuntimeException('The MaxMindBinaryProvider requires maxmind\'s lib to be installed and loaded. Have you included geoipcity.inc file?');
        }

        if (false === is_file($datFile)) {
            throw new InvalidArgumentException(sprintf('Given MaxMind dat file "%s" does not exist.', $datFile));
        }

        if (false === is_readable($datFile)) {
            throw new InvalidArgumentException(sprintf('Given MaxMind dat file "%s" does not readable.', $datFile));
        }

        $this->datFile  = $datFile;
        $this->openFlag = null === $openFlag ? GEOIP_STANDARD : $openFlag;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (false === filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The MaxMindBinaryProvider does not support street addresses.');
        }

        $geoIp       = geoip_open($this->datFile, $this->openFlag);
        $geoIpRecord = GeoIP_record_by_addr($geoIp, $address);

        geoip_close($geoIp);

        if (false === $geoIpRecord instanceof \geoiprecord) {
            throw new NoResultException(sprintf('No results found for IP address %s', $address));
        }

        return array($this->fixEncoding(array_merge($this->getDefaults(), array(
            'countryCode' => $geoIpRecord->country_code,
            'country'     => $geoIpRecord->country_name,
            'region'      => $geoIpRecord->region,
            'city'        => $geoIpRecord->city,
            'latitude'    => $geoIpRecord->latitude,
            'longitude'   => $geoIpRecord->longitude,
        ))));
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The MaxMindBinaryProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'maxmind_binary';
    }
}
