<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Http\Provider;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Provider\AbstractProvider;
use Http\Message\MessageFactory;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class AbstractHttpProvider extends AbstractProvider
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
        $this->client = $client;
        $this->messageFactory = $factory ?: MessageFactoryDiscovery::find();
    }

    /**
     * Get URL and return contents. If content is empty, an exception will be thrown.
     *
     * @param string $url
     *
     * @return string
     *
     * @throws InvalidServerResponse
     */
    protected function getUrlContents(string $url): string
    {
        $request = $this->getRequest($url);

        return $this->getParsedResponse($request);
    }

    /**
     * @param string $url
     *
     * @return RequestInterface
     */
    protected function getRequest(string $url): RequestInterface
    {
        return $this->getMessageFactory()->createRequest('GET', $url);
    }

    /**
     * Send request and return contents. If content is empty, an exception will be thrown.
     *
     * @param RequestInterface $request
     *
     * @return string
     *
     * @throws InvalidServerResponse
     */
    protected function getParsedResponse(RequestInterface $request): string
    {
        $response = $this->getHttpClient()->sendRequest($request);

        $statusCode = $response->getStatusCode();
        if (401 === $statusCode || 403 === $statusCode) {
            throw new InvalidCredentials();
        } elseif (429 === $statusCode) {
            throw new QuotaExceeded();
        } elseif ($statusCode >= 300) {
            throw InvalidServerResponse::create((string) $request->getUri(), $statusCode);
        }

        $body = (string) $response->getBody();
        if (empty($body)) {
            throw InvalidServerResponse::emptyResponse((string) $request->getUri());
        }

        return $body;
    }

    /**
     * Returns the HTTP adapter.
     *
     * @return HttpClient
     */
    protected function getHttpClient(): HttpClient
    {
        return $this->client;
    }

    /**
     * @return MessageFactory
     */
    protected function getMessageFactory(): MessageFactory
    {
        return $this->messageFactory;
    }
}
