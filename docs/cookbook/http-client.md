# Configuring the HTTP client

The Geocoder is decoupled from the HTTP client that sends the HTTP messages. This means
that you are responsible for configuring the HTTP client. Usually the default configuration
is good enough but sometime you may want to do something differently.

How you configure the client differs between different clients below are two examples,
one with [Guzzle 7 client](https://github.com/guzzle/guzzle) and one with the
[cURL client](https://github.com/php-http/curl-client).

## Guzzle 7

```php
use GuzzleHttp\Client;
use Geocoder\Provider\GoogleMaps;

$config = [
    'timeout' => 2.0,
    'verify' => false,
];

$client = new Client($config);
$geocoder = new GoogleMaps($client);

$geocoder->geocode(...);
```


## cURL

```php
use Http\Client\Curl\Client;
use Geocoder\Provider\GoogleMaps;

$options = [
    CURLOPT_CONNECTTIMEOUT => 2,
    CURLOPT_SSL_VERIFYPEER => false,
];

$client  = new Client(null, null, $options);
$geocoder = new GoogleMaps($client);

$geocoder->geocode(...);
```
