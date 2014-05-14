<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\UnsupportedException;
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\QuotaExceededException;
use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderCaProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = '%s://geocoder.ca/?geoit=xml&locate=%s&auth=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = '%s://geocoder.ca/?geoit=xml&reverse=1&latt=%F&longt=%F&auth=%s';

    /**
     * @var string
     */
    private $scheme = 'http';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param bool                 $useSsl  Whether to use an SSL connection (optional).
     * @param string               $apiKey  An API key (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $useSsl = false, $apiKey = null)
    {
        parent::__construct($adapter);

        $this->scheme = $useSsl ? 'https' : $this->scheme;
        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The GeocoderCaProvider does not support IP addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->scheme, urlencode($address), $this->apiKey);

        try {
            $content = $this->handleQuery($query);
        } catch (InvalidCredentialsException $e) {
            throw $e;
        } catch (QuotaExceededException $e) {
            throw $e;
        } catch (NoResultException $e) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        return array(array_merge($this->getDefaults(), array(
            'latitude'  => $this->getNodeValue($content->getElementsByTagName('latt')),
            'longitude' => $this->getNodeValue($content->getElementsByTagName('longt'))
        )));
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->scheme, $coordinates[0], $coordinates[1], $this->apiKey);

        try {
            $content = $this->handleQuery($query);
        } catch (InvalidCredentialsException $e) {
            throw $e;
        } catch (QuotaExceededException $e) {
            throw $e;
        } catch (NoResultException $e) {
            throw new NoResultException(sprintf('Could not resolve coordinates %s', implode(', ', $coordinates)));
        }

        return array(array_merge($this->getDefaults(), array(
            'latitude'     => $this->getNodeValue($content->getElementsByTagName('latt')),
            'longitude'    => $this->getNodeValue($content->getElementsByTagName('longt')),
            'streetNumber' => $this->getNodeValue($content->getElementsByTagName('stnumber')),
            'streetName'   => $this->getNodeValue($content->getElementsByTagName('staddress')),
            'city'         => $this->getNodeValue($content->getElementsByTagName('city')),
            'zipcode'      => $this->getNodeValue($content->getElementsByTagName('postal')),
            'cityDistrict' => $this->getNodeValue($content->getElementsByTagName('prov')),
        )));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geocoder_ca';
    }

    /**
     * @param \DOMNodeList
     *
     * @return string
     */
    private function getNodeValue(\DOMNodeList $element)
    {
        return $element->length ? $element->item(0)->nodeValue : null;
    }

    /**
     * @param  string                      $query
     * @throws InvalidCredentialsException
     * @throws QuotaExceededException
     * @throws NoResultException
     * @return \DOMDocument
     */
    private function handleQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        $doc = new \DOMDocument;
        if (!@$doc->loadXML($content) || $doc->getElementsByTagName('error')->length) {
            switch ($this->getNodeValue($doc->getElementsByTagName('code'))) {
                case '001':
                case '003':
                    throw new InvalidCredentialsException(sprintf('Invalid authentification token %s', $query));
                case '002':
                    throw new QuotaExceededException(sprintf('Account ran out of credits %s', $query));
                default:
                    throw new NoResultException;
            }
        }

        return $doc;
    }
}
