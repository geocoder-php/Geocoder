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
class CurlHttpAdapter extends AbstractHttpAdapter implements HttpAdapterInterface
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
        curl_setopt($c, CURLOPT_HEADER, 1);


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
        $headerSize = curl_getinfo($c, CURLINFO_HEADER_SIZE);
        curl_close($c);

        if (false === $content) {
            $this->setHeaders([]);
            return null;
        }
        $header = substr($content, 0, $headerSize);
        $this->setHeaders($this->parseHeader($header));
        $body = substr($content, $headerSize);

        return $body;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'curl';
    }

    /**
     * Parses HTTP header into an array of name => value headers
     */
    protected function parseHeader($header)
    {
        $headers = explode("\n", $header);
        $return = [];
        foreach ($headers as $header) {
            if (!preg_match('#^([^:]+): (.*)$#', $header, $match)) {
                continue;
            }
            $return[strtolower(trim($match[1]))] = trim($match[2]);
        }
        return $return;
    }
}
