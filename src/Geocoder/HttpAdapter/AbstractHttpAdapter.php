<?php
/**
 * @author Mikhail Romanov <mikhail.romanov@rocket-internet.de>
 */

namespace Geocoder\HttpAdapter;

use Geocoder\Exception\ExtensionNotLoadedException;


abstract class AbstractHttpAdapter
{
    protected $headers = [];

    /**
     * Returns HTTP headers from the last request.
     *
     * @return array list of headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns HTTP header from the last request
     *
     * @param string $name name of the desired header
     *
     * @return string|boolean returns false if header was not available, string otherwise
     */
    public function getHeader($name)
    {
        if (!isset($this->headers[$name])) {
            return false;
        }
        return $this->headers[$name];
    }

    /**
     * Saves headers for future use
     */
    protected function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
}
