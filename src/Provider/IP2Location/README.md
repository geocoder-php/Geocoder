# IP2Location Geocoder provider
[![Build Status](https://travis-ci.org/geocoder-php/ip2location-provider.svg?branch=master)](http://travis-ci.org/geocoder-php/ip2location-provider)
[![Latest Stable Version](https://poser.pugx.org/geocoder-php/ip2location-provider/v/stable)](https://packagist.org/packages/geocoder-php/ip2location-provider)
[![Total Downloads](https://poser.pugx.org/geocoder-php/ip2location-provider/downloads)](https://packagist.org/packages/geocoder-php/ip2location-provider)
[![Monthly Downloads](https://poser.pugx.org/geocoder-php/ip2location-provider/d/monthly.png)](https://packagist.org/packages/geocoder-php/ip2location-provider)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/ip2location-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/ip2location-provider)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/ip2location-provider.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/ip2location-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the IP2Location provider from the PHP Geocoder. This is a **READ ONLY** repository. See the
[main repo](https://github.com/geocoder-php/Geocoder) for information and documentation. 

### Install

```bash
composer require geocoder-php/ip2location-provider
```

### Note

This provider requires IP2Location™ [IP Geolocation Web Service](https://www.ip2location.com/web-service/ip2location) subscription. It is a paid solution with high accuracy. For free solution, please use our free [IpInfoDB](https://github.com/geocoder-php/Geocoder#ip) project. Ipinfodb is using [IP2Location LITE](https://lite.ip2location.com/) database which has less accuracy at city level.

Please note that this provider is querying IP2Location Web Service **WS9** package and each query will cost **4 credits**.

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
