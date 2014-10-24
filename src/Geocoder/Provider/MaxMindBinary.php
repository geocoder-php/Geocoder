<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResult;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\FunctionNotFound;
use Geocoder\Exception\UnsupportedOperation;

class MaxMindBinary extends AbstractProvider implements Provider
{
    /**
     * @var string
     */
    private $datFile;

    /**
     * @var int|null
     */
    private $openFlag;

    /**
     * @param string   $datFile
     * @param int|null $openFlag
     *
     * @throws FunctionNotFound If maxmind's lib not installed.
     * @throws InvalidArgument  If dat file is not correct (optional).
     */
    public function __construct($datFile, $openFlag = null)
    {
        if (false === function_exists('geoip_open')) {
            throw new FunctionNotFound(
                'geoip_open',
                'The MaxMindBinaryProvider requires maxmind\'s lib to be installed and loaded. Have you included geoip.inc file?'
            );
        }

        if (false === function_exists('GeoIP_record_by_addr')) {
            throw new FunctionNotFound(
                'GeoIP_record_by_addr',
                'The MaxMindBinaryProvider requires maxmind\'s lib to be installed and loaded. Have you included geoipcity.inc file?'
            );
        }

        if (false === is_file($datFile)) {
            throw new InvalidArgument(sprintf('Given MaxMind dat file "%s" does not exist.', $datFile));
        }

        if (false === is_readable($datFile)) {
            throw new InvalidArgument(sprintf('Given MaxMind dat file "%s" does not readable.', $datFile));
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
            throw new UnsupportedOperation('The MaxMindBinaryProvider does not support street addresses.');
        }

        $geoIp       = geoip_open($this->datFile, $this->openFlag);
        $geoIpRecord = GeoIP_record_by_addr($geoIp, $address);

        geoip_close($geoIp);

        if (false === $geoIpRecord instanceof \geoiprecord) {
            throw new NoResult(sprintf('No results found for IP address %s', $address));
        }

        return array($this->fixEncoding(array_merge($this->getDefaults(), array(
            'countryCode' => $geoIpRecord->country_code,
            'country'     => $geoIpRecord->country_name,
            'region'      => $geoIpRecord->region,
            'locality'    => $geoIpRecord->city,
            'latitude'    => $geoIpRecord->latitude,
            'longitude'   => $geoIpRecord->longitude,
        ))));
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedOperation('The MaxMindBinaryProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'maxmind_binary';
    }
}
