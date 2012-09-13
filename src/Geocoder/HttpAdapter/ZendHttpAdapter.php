<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\HttpAdapter;

use Zend\Http\Client;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ZendHttpAdapter implements HttpAdapterInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client Client object
     */
    public function __construct(Client $client = null)
    {
        $this->client = null === $client ? new Client() : $client;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent($url)
    {
        try {
            $response = $this->client->setUri($url)->send();

            if ($response->isSuccess()) {
                $content = $response->getBody();
            } else {
                $content = null;
            }
        } catch (\Exception $e) {
            $content = null;
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'zend';
    }
}
