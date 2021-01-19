# Rate limiting API requests

Some APIs (for example, the Nominatim API) have usage policies dictating the amount of requests you are allowed to make.
To avoid hitting these limits, it's recommended to use `ProviderCache` provider.

Since the limits on the Nominatim API are not hard limits, rate limiting outbound requests is one solution.
This could also be used to ensure that massive bills are not accidentally created (for example, due to a bug/loop issue).

## Example using Spatie's Guzzle Rate Limiter with Geocoder

First off, you'll need to install the package:
```bash
composer require spatie/guzzle-rate-limiter-middleware
```

Then create a geocoder instance using the rate limit (and in this example, the cache provider)

```php
$stack = \GuzzleHttp\HandlerStack::create();
$stack->push(\Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware::perSecond(1));

$httpClient = new \GuzzleHttp\Client(['handler' => $stack, 'timeout' => 30.0]);
$psr6Cache = new \Cache\Adapter\PHPArray\ArrayCachePool();
$provider = new \Geocoder\Provider\Nominatim\Nominatim($httpClient, 'https://nominatim.openstreetmap.org', 'Geocoder test');
$cachedProvider = new \Geocoder\Provider\Cache\ProviderCache($provider, $psr6Cache);
$geocoder = new \Geocoder\StatefulGeocoder($cachedProvider, 'en');

$formatter = new \Geocoder\Formatter\StringFormatter();

$gps_coords = [
    [
        'lat' => 52.516275,
        'lgn' => 13.377704,
    ],
    [
        'lat' => 51.503396,
        'lgn' => -0.12764,
    ],
    [
        'lat' => 52.516275,
        'lgn' => 13.377704,
    ]
];
foreach ($gps_coords as $gps_coord) {
    $result_list = $geocoder->reverseQuery(\Geocoder\Query\ReverseQuery::fromCoordinates($gps_coord['lat'], $gps_coord['lgn']));
    $result = $result_list->first();
    echo $formatter->format($result, '%S %n, %z %L').PHP_EOL;
}
```
