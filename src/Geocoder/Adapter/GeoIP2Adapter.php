<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Adapter;

use GeoIp2\ProviderInterface;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Provider\LocaleTrait;

/**
 * @author Jens Wiese <jens@howtrueisfalse.de>
 */
class GeoIP2Adapter
{
    /**
     * GeoIP2 models (e.g. city or country)
     */
    const GEOIP2_MODEL_CITY    = 'city';
    const GEOIP2_MODEL_COUNTRY = 'country';

    use LocaleTrait;

    /**
     * @var ProviderInterface
     */
    protected $geoIp2Provider;

    /**
     * @var string
     */
    protected $geoIP2Model;

    /**
     * @param \GeoIp2\ProviderInterface $geoIpProvider
     * @param string                    $geoIP2Model   (e.g. self::GEOIP2_MODEL_CITY)
     */
    public function __construct(ProviderInterface $geoIpProvider, $geoIP2Model = self::GEOIP2_MODEL_CITY)
    {
        $this->geoIp2Provider = $geoIpProvider;

        if (false === $this->isSupportedGeoIP2Model($geoIP2Model)) {
            throw new UnsupportedOperation(
                sprintf('Model "%s" is not available.', $geoIP2Model)
            );
        }

        $this->geoIP2Model = $geoIP2Model;
    }

    /**
     * Returns the content fetched from a given resource.
     *
     * @param  string $url (e.g. file://database?127.0.0.1)
     * @return string
     */
    public function getContent($url)
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgument(
                sprintf('"%s" must be called with a valid url. Got "%s" instead.', __METHOD__, $url)
            );
        }

        $ipAddress = parse_url($url, PHP_URL_QUERY);

        if (false === filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw new InvalidArgument('URL must contain a valid query-string (an IP address, 127.0.0.1 for instance)');
        }

        $result = $this->geoIp2Provider
            ->{$this->geoIP2Model}($ipAddress)
            ->jsonSerialize();

        return json_encode($result);
    }

    /**
     * Returns the name of the Adapter.
     *
     * @return string
     */
    public function getName()
    {
        return 'maxmind_geoip2';
    }

    /**
     * Returns whether method is supported by GeoIP2
     *
     * @param  string $method
     * @return bool
     */
    protected function isSupportedGeoIP2Model($method)
    {
        $availableMethods = array(
            self::GEOIP2_MODEL_CITY,
            self::GEOIP2_MODEL_COUNTRY,
        );

        return in_array($method, $availableMethods);
    }
}
