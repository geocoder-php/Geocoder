<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AbstractHttpProvider extends AbstractProvider
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @param HttpClient $client An HTTP adapter
     */
    public function __construct(HttpClient $client = null, MessageFactory $factory = null)
    {
        parent::__construct();

        $this->client = $client ?: HttpClientDiscovery::find();
        $this->factory = $factory ?: MessageFactoryDiscovery::find();
    }

    /**
     * Returns the HTTP adapter.
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->client;
    }
}
