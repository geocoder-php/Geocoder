<?php

namespace Geocoder\Tests;

use Geocoder\HttpAdapter\HttpAdapterInterface;

class CachedResponseAdapter implements HttpAdapterInterface
{
    private $adapter;

    private $cacheDir;

    public function __construct(HttpAdapterInterface $adapter, $cacheDir = '.cached_responses')
    {
        $this->adapter  = $adapter;
        $this->cacheDir = $cacheDir;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent($url)
    {
        $file = sprintf('%s/%s/%s', realpath(__DIR__ . '/../../'), $this->cacheDir, sha1($url));

        if (is_file($file) && is_readable($file)) {
            $response = unserialize(file_get_contents($file));

            if (!empty($response)) {
                return $response;
            }
        }

        $response = $this->adapter->getContent($url);
        file_put_contents($file, serialize($response));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'cached_response';
    }
}
