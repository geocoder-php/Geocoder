<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\HttpAdapter;

use Geocoder\Exception\HttpException;
use Geocoder\Exception\ExtensionNotLoadedException;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class SocketHttpAdapter implements HttpAdapterInterface
{
    /**
     * @var integer
     */
    const MAX_REDIRECTS = 5;

    /**
     * @var integer
     */
    private $redirectsRemaining = self::MAX_REDIRECTS;

    /**
     * {@inheritDoc}
     */
    public function getContent($url)
    {
        $info = parse_url($url);

        $hostname = $info['host'];
        $port     = isset($info['port']) ? $info['port'] : 80;
        $path     = sprintf('%s%s',
            isset($info['path'])  ? $info['path']        : '/',
            isset($info['query']) ? '?' . $info['query'] : ''
        );

        $socketHandle = $this->createSocket($hostname, $port, 30);

        if (!fwrite($socketHandle, $this->buildHttpRequest($path, $hostname))) {
            throw new ExtensionNotLoadedException('Could not send the request');
        }

        $httpResponse = $this->getParsedHttpResponse($socketHandle);

        if ($httpResponse['headers']['status'] === 301 && isset($httpResponse['headers']['location'])) {
            if (--$this->redirectsRemaining) {
                return $this->getContent($httpResponse['headers']['location']);
            } else {
                throw new HttpException('Too Many Redirects');
            }
        } else {
            $this->redirectsRemaining = self::MAX_REDIRECTS;
        }

        if ($httpResponse['headers']['status'] !== 200) {
            throw new HttpException(sprintf('The server return a %s status.', $httpResponse['headers']['status']));
        }

        return $httpResponse['content'];
    }

    /**
     * This method strictly doesn't need to exist but can act as a "seam" for substituting fake sockets in test.
     * This would require a subclass that overloads the method and returns the fake socket.
     *
     * @param string  $hostname The hostname.
     * @param string  $port     The port number.
     * @param integer $timeout  The timeout.
     *
     * @return resource
     * @throws HttpException
     */
    protected function createSocket($hostname, $port, $timeout)
    {
        $socketHandle = fsockopen($hostname, $port, $errno, $errstr, $timeout) ?: null;

        //verify handle
        if (null === $socketHandle) {
            throw new HttpException(sprintf('Could not connect to socket. (%s)', $errstr));
        }

        return $socketHandle;
    }

    /**
     * Build the HTTP 1.1 request headers from the given inputs.
     *
     * @param string $path     The path.
     * @param string $hostname The hostname.
     *
     * @return string
     */
    protected function buildHttpRequest($path, $hostname)
    {
        $r = array();
        $r[] = "GET {$path} HTTP/1.1";
        $r[] = "Host: {$hostname}";
        $r[] = "Connection: Close";
        $r[] = "User-Agent: Geocoder PHP-Library";
        $r[] = "\r\n";

        return implode("\r\n", $r);
    }

    /**
     * Given a resource parse the contents into its component parts (headers/contents)
     *
     * @param resource $socketHandle
     *
     * @return array
     */
    protected function getParsedHttpResponse($socketHandle)
    {
        $httpResponse = array();
        $httpResponse['headers'] = array();
        $httpResponse['content'] = '';

        $reachedEndOfHeaders = false;

        while (!feof($socketHandle)) {
            $line = trim(fgets($socketHandle));
            if (!$line) {
                $reachedEndOfHeaders = true;
                continue;
            }
            if (!$reachedEndOfHeaders) {
                if (preg_match('@^HTTP/\d\.\d\s*(\d+)\s*.*$@', $line, $matches)) {
                    $httpResponse['headers']['status'] = (integer) $matches[1];
                } elseif (preg_match('@^([^:]+): (.+)$@', $line, $matches)) {
                    $httpResponse['headers'][strtolower($matches[1])] = trim($matches[2]);
                }
            } else {
                $httpResponse['content'] .= $line;
            }
        }

        return $httpResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'socket';
    }
}
