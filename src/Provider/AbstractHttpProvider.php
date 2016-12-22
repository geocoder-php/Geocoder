<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Http\Message\MessageFactory;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Client\HttpClient;

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
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @param HttpClient          $client
     * @param MessageFactory|null $factory
     */
    public function __construct(HttpClient $client, MessageFactory $factory = null)
    {
        parent::__construct();

        $this->client = $client;
        $this->messageFactory = $factory;
    }

    /**
     * Returns the HTTP adapter.
     *
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        return $this->client;
    }

    /**
     * @return MessageFactory
     */
    protected function getMessageFactory()
    {
        if ($this->messageFactory === null) {
            $this->messageFactory = MessageFactoryDiscovery::find();
        }

        return $this->messageFactory;
    }

    /**
     * @param HttpClient $client
     *
     * @return AbstractHttpProvider
     */
    public function setClient(HttpClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param MessageFactory $messageFactory
     *
     * @return AbstractHttpProvider
     */
    public function setMessageFactory(MessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;

        return $this;
    }
}
