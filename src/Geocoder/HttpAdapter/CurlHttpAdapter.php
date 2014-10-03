<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\HttpAdapter;

use Geocoder\Exception\ExtensionNotLoadedException;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class CurlHttpAdapter implements HttpAdapterInterface
{
    private $timeout;

    private $connectTimeout;

    private $userAgent;

    /**
     * Array for bulk setting of curl options
     * @see http://php.net/manual/en/curl.constants.php
     * @var array
     */
    private $options;

    public function __construct($timeout = null, $connectTimeout = null, $userAgent = null, $options = array())
    {
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->userAgent = $userAgent;
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent($url)
    {
        if (!function_exists('curl_init')) {
            throw new ExtensionNotLoadedException('cURL has to be enabled.');
        }

        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);

        if ($this->timeout) {
            curl_setopt($c, CURLOPT_TIMEOUT, $this->timeout);
        }

        if ($this->connectTimeout) {
            curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }

        if ($this->userAgent) {
            curl_setopt($c, CURLOPT_USERAGENT, $this->userAgent);
        }

        if ($this->options && is_array($this->options) && count($this->options)>0) {
            curl_setopt_array($c, $this->options);
        }

        $content = curl_exec($c);
        curl_close($c);

        if (false === $content) {
            $content = null;
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'curl';
    }
}
