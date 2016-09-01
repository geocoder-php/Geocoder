# Caching responses

Many of the APIs are not free so it is a good idea to cache the responses so you 
are not paying for the same information twice. The caching is out of scope for this 
library but we will show you an example how to properly cache responses with the
HTTPlug [cache plugin](http://php-http.readthedocs.io/en/latest/plugins/cache.html):

```php
use Cache\Adapter\Redis\RedisCachePool;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use Http\Client\Common\PluginClient;
use Geocoder\Provider\GoogleMaps;

// Get a PSR-6 cache pool
$client = new \Redis();
$client->connect('127.0.0.1', 6379);
$pool = new RedisCachePool($client);

// Give the cache pool to the cache plugin and congure it to ignore
// cache headers and store the response for one year.
$cachePlugin = new CachePlugin($pool, StreamFactoryDiscovery::find(), [
    'respect_cache_headers' => false,
    'default_ttl' => null,
    'cache_lifetime' => 86400*365
]);
    
$adapter = new GuzzleClient();    
$pluginClient = new PluginClient($adapter, [$cachePlugin]);

// Get a geocoder
$geocoder = new GoogleMaps($pluginClient, 'en', null, null, true, 'api-key');

// Query Google Maps servers
$result0 = $geocoder->geocode('foobar');

// This will be retrieved from the cache and not hit Google's servers
$result1 = $geocoder->geocode('foobar');
```
