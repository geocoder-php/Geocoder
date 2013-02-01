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

/**
 * Google Maps for Business
 * https://developers.google.com/maps/documentation/business/
 *
 * @author Jason Bouffard <jpb0104@gmail.com>
 */
class GoogleMapsBusinessProvider extends GoogleMapsProvider
{
    /**
     * @var string
     */
    private $clientId = null;

    /**
     * @var string
     */
    private $privateKey = null;

    /**
     * @param HttpAdapterInterface $adapter    An HTTP adapter.
     * @param string               $clientId   Your Client ID.
     * @param string               $privateKey Your Private Key (optional).
     * @param string               $locale     A locale (optional).
     * @param string               $region     Region biasing (optional).
     * @param bool                 $useSsl     Whether to use an SSL connection (optional)
     */
    public function __construct(HttpAdapterInterface $adapter, $clientId, $privateKey = null, $locale = null,
        $region = null, $useSsl = false)
    {
        parent::__construct($adapter, $locale, $region, $useSsl);

        $this->clientId   = $clientId;
        $this->privateKey = $privateKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'google_maps_business';
    }

    /**
     * {@inheritDoc}
     */
    protected function buildQuery($query)
    {
        $query = parent::buildQuery($query);
        $query = sprintf('%s&client=%s', $query, $this->clientId);

        if (null !== $this->privateKey) {
            $query = $this->signQuery($query);
        }

        return $query;
    }

    /**
     * Sign a URL with a given crypto key
     * Note that this URL must be properly URL-encoded
     * src: http://gmaps-samples.googlecode.com/svn/trunk/urlsigning/UrlSigner.php-source
     *
     * @param string $query Query to be signed
     *
     * @return string $query Query with signature appended.
     */
    protected function signQuery($query)
    {
        $url = parse_url($query);

        $urlPartToSign = $url['path'] . '?' . $url['query'];

        // Decode the private key into its binary format
        $decodedKey = base64_decode(str_replace(array('-', '_'), array('+', '/'), $this->privateKey));

        // Create a signature using the private key and the URL-encoded
        // string using HMAC SHA1. This signature will be binary.
        $signature = hash_hmac('sha1', $urlPartToSign, $decodedKey, true);

        $encodedSignature = str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));

        return sprintf('%s&signature=%s', $query, $encodedSignature);
    }
}
