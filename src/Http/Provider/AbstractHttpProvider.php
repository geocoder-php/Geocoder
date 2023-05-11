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
use Http\Discovery\Psr17Factory;
use Http\Message\MessageFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
abstract class AbstractHttpProvider extends AbstractProvider
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestFactoryInterface&StreamFactoryInterface)|MessageFactory
     */
    private $messageFactory;

    /**
     * @param Psr17Factory|MessageFactory|null $factory Passing a MessageFactory is @deprecated
     */
    public function __construct(ClientInterface $client, MessageFactory|Psr17Factory $factory = null)
    {
        $this->client = $client;
        $this->messageFactory = $factory ?? ($client instanceof RequestFactoryInterface && $client instanceof StreamFactoryInterface ? $client : new Psr17Factory());
    }

    /**
     * Get URL and return contents. If content is empty, an exception will be thrown.
     *
     * @throws InvalidServerResponse
     */
    protected function getUrlContents(string $url): string
    {
        $request = $this->getRequest($url);

        return $this->getParsedResponse($request);
    }

    protected function getRequest(string $url): RequestInterface
    {
        return $this->createRequest('GET', $url);
    }

    /**
     * @param array<string,string|string[]> $headers
     */
    protected function createRequest(string $method, string $uri, array $headers = [], string $body = null): RequestInterface
    {
        if ($this->messageFactory instanceof MessageFactory) {
            return $this->messageFactory->createRequest($method, $uri, $headers, $body);
        }

        $request = $this->messageFactory->createRequest($method, $uri);

        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        if (null === $body) {
            return $request;
        }

        $stream = $this->messageFactory->createStream($body);

        if ($stream->isSeekable()) {
            $stream->seek(0);
        }

        return $request->withBody($stream);
    }

    /**
     * Send request and return contents. If content is empty, an exception will be thrown.
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
        if ('' === $body) {
            throw InvalidServerResponse::emptyResponse((string) $request->getUri());
        }

        return $body;
    }

    /**
     * Returns the HTTP adapter.
     */
    protected function getHttpClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @deprecated Use createRequest instead
     */
    protected function getMessageFactory(): MessageFactory
    {
        if ($this->messageFactory instanceof MessageFactory) {
            return $this->messageFactory;
        }

        $factory = $this->messageFactory instanceof ResponseFactoryInterface ? $this->messageFactory : new Psr17Factory();

        return new class($factory) implements MessageFactory {
            public function __construct(
                /**
                 * @param RequestFactoryInterface&ResponseFactoryInterface&StreamFactoryInterface $factory
                 */
                private RequestFactoryInterface|ResponseFactoryInterface|StreamFactoryInterface $factory,
            ) {
            }

            /**
             * @param string                               $method
             * @param string|UriInterface                  $uri
             * @param array<string,string|string[]>        $headers
             * @param resource|string|StreamInterface|null $body
             * @param string                               $protocolVersion
             */
            public function createRequest($method, $uri, array $headers = [], $body = null, $protocolVersion = '1.1'): RequestInterface
            {
                $request = $this->factory->createRequest($method, $uri);

                foreach ($headers as $name => $value) {
                    $request = $request->withAddedHeader($name, $value);
                }

                if (null !== $body) {
                    $request = $request->withBody($this->createStream($body));
                }

                return $request->withProtocolVersion($protocolVersion);
            }

            /**
             * @param int                                  $statusCode
             * @param string|null                          $reasonPhrase
             * @param array<string,string|string[]>        $headers
             * @param resource|string|StreamInterface|null $body
             * @param string                               $protocolVersion
             */
            public function createResponse($statusCode = 200, $reasonPhrase = null, array $headers = [], $body = null, $protocolVersion = '1.1'): ResponseInterface
            {
                $response = $this->factory->createResponse($statusCode, $reasonPhrase);

                foreach ($headers as $name => $value) {
                    $response = $response->withAddedHeader($name, $value);
                }

                if (null !== $body) {
                    $response = $response->withBody($this->createStream($body));
                }

                return $response->withProtocolVersion($protocolVersion);
            }

            /**
             * @param string|resource|StreamInterface|null $body
             */
            private function createStream($body = ''): StreamInterface
            {
                if ($body instanceof StreamInterface) {
                    return $body;
                }

                if (\is_string($body ?? '')) {
                    $stream = $this->factory->createStream($body ?? '');
                } elseif (\is_resource($body)) {
                    $stream = $this->factory->createStreamFromResource($body);
                } else {
                    throw new \InvalidArgumentException(sprintf('"%s()" expects string, resource or StreamInterface, "%s" given.', __METHOD__, get_debug_type($body)));
                }

                if ($stream->isSeekable()) {
                    $stream->seek(0);
                }

                return $stream;
            }
        };
    }
}
