<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\HttpAdapter;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Http adapter for the Guzzle framework
 *
 * @author Michael Dowling <michael@guzzlephp.org>
 * @link   http://www.guzzlephp.org
 */
class Guzzle4HttpAdapter implements HttpAdapterInterface
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param ClientInterface $client Client object
     */
    public function __construct(ClientInterface $client = null)
    {
        $this->client = null === $client ? new Client() : $client;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent($url)
    {
        $response = $this->client->get($url);

        return (string) $response->getBody();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'guzzle4';
    }
}
