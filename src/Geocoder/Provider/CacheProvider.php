<?php
namespace Geocoder\Provider;

use Geocoder\CacheAdapter\CacheInterface;

/**
 *
 */
class CacheProvider implements ProviderInterface {

    protected $_cacheAdapter;

    protected $_provider;

    public function __construct(CacheInterface $cache, ProviderInterface $provider)
    {
        $this->_cacheAdapter = $cache;
        $this->_provider = $provider;
    }

    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param string $address   An address (IP or street).
     * @return array
     */
    public function getGeocodedData($address)
    {
        $key = sha1($address);

        $result = $this->_cacheAdapter->retrieve($key);

        if ( null === $result ) {
            $result = $this->_provider->getGeocodedData($address);
            $this->_cacheAdapter->store($key, $result);
        }

        return $result;
    }

    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param array $coordinates    Coordinates (latitude, longitude).
     * @return array
     */
    public function getReversedData(array $coordinates)
    {
        $key = sha1(serialize($coordinates));

        $result = $this->_cacheAdapter->retrieve($key);

        if ( null === $result ) {
            $result = $this->_provider->getReversedData($coordinates);
            $this->_cacheAdapter->store($key, $result);
        }

        return $result;
    }

    /**
     * Returns the provider's name.
     *
     * @return string
     */
    public function getName()
    {
        return 'cache';
    }

}
