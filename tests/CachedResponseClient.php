<?php

namespace Geocoder\Tests;

use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;

class CachedResponseClient implements HttpClient
{
    private $delegate;

    private $useCache;

    private $apiKey;

    private $cacheDir;

    public function __construct(HttpClient $delegate, $useCache = false, $apiKey = null, $cacheDir = '.cached_responses')
    {
        $this->delegate  = $delegate;
        $this->useCache = $useCache;
        $this->apiKey   = $apiKey;
        $this->cacheDir = $cacheDir;
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $url = (string) $request->getUri();
        if ($this->apiKey) {
            $url = str_replace($this->apiKey, '[apikey]', $url);
        }

        $file = sprintf('%s/%s/%s', __DIR__, $this->cacheDir, sha1($url));

        if ($this->useCache && is_file($file) && is_readable($file)) {
            return new Response(200, [], Psr7\stream_for(unserialize(file_get_contents($file))));
        }

        $response = $this->delegate->sendRequest($request);

        if ($this->useCache) {
            file_put_contents($file, serialize($response->getBody()->getContents()));
        }

        return $response;
    }
}
