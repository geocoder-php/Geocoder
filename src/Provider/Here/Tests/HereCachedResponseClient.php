<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Here\Tests;

use Geocoder\IntegrationTest\CachedResponseClient;
use Http\Client\HttpClient;
use Nyholm\Psr7\Factory\StreamFactory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Serve responses from local file cache.
 *
 * @author Sébastien Barré <sebastien@sheub.eu> override the provider-integration-tests: CachedResponseClient class
 */
class HereCachedResponseClient extends CachedResponseClient
{
    /**
     * @var HttpClient
     */
    private $delegate;

    /**
     * @var null|string
     */
    private $apiKey;

    /**
     * @var null|string
     */
    private $appCode;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param HttpClient  $delegate
     * @param string      $cacheDir
     * @param string|null $apiKey
     * @param string|null $appCode
     */
    public function __construct(HttpClient $delegate, $cacheDir, $apiKey = null, $appCode = null)
    {
        $this->delegate = $delegate;
        $this->cacheDir = $cacheDir;
        $this->apiKey = $apiKey;
        $this->appCode = $appCode;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $url = (string) $request->getUri();
        $host = (string) $request->getUri()->getHost();
        if (!empty($this->apiKey)) {
            $url = str_replace($this->apiKey, '[apikey]', $url);
        }
        if (!empty($this->appCode)) {
            $url = str_replace($this->appCode, '[appCode]', $url);
        }

        $file = sprintf('%s/%s_%s', $this->cacheDir, $host, sha1($url));
        if (is_file($file) && is_readable($file)) {
            return new Response(200, [], (new StreamFactory())->createStream(unserialize(file_get_contents($file))));
        }

        $response = $this->delegate->sendRequest($request);
        file_put_contents($file, serialize($response->getBody()->getContents()));

        return $response;
    }
}
