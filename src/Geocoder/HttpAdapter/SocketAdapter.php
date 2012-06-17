<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\HttpAdapter;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class SocketAdapter implements HttpAdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getContent($url)
    {
        $info = parse_url($url);

        $scheme   = isset($info['scheme']) ? $info['scheme'] : 'http';
        $hostname = $info['host'];
        $port     = (isset($info['port']) ? $info['port'] : 80);
        $path     = (isset($info['path']) ? $info['path'] : '/');
        $query    = (isset($info['query']) ? '?'.$info['query'] : '');

        try {
            $handle = fsockopen($hostname, $port, $errno, $errstr, 30);
        } catch (\Exception $e) {
            $handle = null;
        }

        if (null === $handle) {
            throw new \RuntimeException(sprintf('Could not connect to socket. (%s)', $errstr));
        }

        $request = "GET {$path} HTTP/1.1\r\n"
            . "Host: {$hostname}\r\n"
            . "Connection: Close\r\n"
            . "User-Agent: Geocoder PHP-Library\r\n"
            . "\r\n";

        if (!fwrite($handle, $request)) {
            throw new \RuntimeException('Could not send the request');
        }

        $rawHeaders = array();
        $rawContent = '';
        while (!feof($handle)) {
            $line = trim(fgets($handle));
            if (preg_match('@^HTTP/\d\.\d\s*(\d+)\s*.*$@', $line, $matches)) {
                $rawHeaders['status'] = (integer) $matches[1];
            } elseif (preg_match('@^(.*):(.*)@', $line, $matches)) {
                $rawHeaders[strtolower($matches[1])] = trim($matches[2]);
            } else {
                $rawContent .= $line;
            }
        }

        if ($rawHeaders['status'] === 301 && isset($rawHeaders['location'])) {
            return $this->getContent($rawHeaders['location']);
        }

        if ($rawHeaders['status'] !== 200) {
            throw new \RuntimeException(sprintf('The server return a %s status.', $rawHeaders['status']));
        }

        return $rawContent;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'socket';
    }
}
