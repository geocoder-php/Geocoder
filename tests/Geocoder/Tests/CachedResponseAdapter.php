<?php

namespace Geocoder\Tests;

use Ivory\HttpAdapter\AbstractHttpAdapter;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\Message\InternalRequestInterface;
use Ivory\HttpAdapter\Message\Stream\StringStream;
use Ivory\HttpAdapter\Message\RequestInterface;

class CachedResponseAdapter extends AbstractHttpAdapter
{
    private $adapter;

    private $useCache;

    private $apiKey;

    private $cacheDir;

    public function __construct(HttpAdapterInterface $adapter, $useCache = false, $apiKey, $cacheDir = '.cached_responses')
    {
        parent::__construct();

        $this->adapter  = $adapter;
        $this->useCache = $useCache;
        $this->apiKey   = $apiKey;
        $this->cacheDir = $cacheDir;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'cached_response';
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(InternalRequestInterface $internalRequest)
    {
        $url = $internalRequest->getUrl();
        if ($this->apiKey) {
            $url = str_replace($this->apiKey, '[apikey]', $url);
        }
        $file = sprintf('%s/%s/%s', realpath(__DIR__ . '/../../'), $this->cacheDir, sha1($url));

        if ($this->useCache && is_file($file) && is_readable($file)) {
            $content = unserialize(file_get_contents($file));
            $body    = new StringStream($content);

            $response = $this->adapter->getConfiguration()->getMessageFactory()->createResponse(
                200, 'OK', RequestInterface::PROTOCOL_VERSION_1_1, [], $body
            );

            if (!empty($content)) {
                return $response;
            }
        }

        $response = $this->adapter->get($internalRequest->getUrl());

        if ($this->useCache) {
            file_put_contents($file, serialize((string) $response->getBody()));
        }

        return $response;
    }
}
