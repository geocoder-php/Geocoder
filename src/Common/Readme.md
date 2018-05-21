# Common classes for the Geocoder
[![Build Status](https://travis-ci.org/geocoder-php/php-common.svg?branch=master)](http://travis-ci.org/geocoder-php/php-common)
[![Latest Stable Version](https://poser.pugx.org/willdurand/geocoder/v/stable)](https://packagist.org/packages/willdurand/geocoder)
[![Total Downloads](https://poser.pugx.org/willdurand/geocoder/downloads)](https://packagist.org/packages/willdurand/geocoder)
[![Monthly Downloads](https://poser.pugx.org/willdurand/geocoder/d/monthly.png)](https://packagist.org/packages/willdurand/geocoder)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/geocoder-php/php-common.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/php-common)
[![Quality Score](https://img.shields.io/scrutinizer/g/geocoder-php/php-common.svg?style=flat-square)](https://scrutinizer-ci.com/g/geocoder-php/php-common)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

### Note

* This repository is **READ ONLY**
* Post issues and PRs at the main repository: https://github.com/geocoder-php/Geocoder

### History

Just some months before the release of 4.0 of `willdurand/geocoder` we changed the repository to https://github.com/geocoder-php/php-common
from https://github.com/geocoder-php/Geocoder. The new repository will only contain classes and interfaces shared between 
multiple providers. The original repository is still used for issues and pull requests. 

The new repository architecture allows us to use a [git subtree split](https://www.subtreesplit.com) from geocoder-php/Geocoder
to geocoder-php/php-common and to each provider. 

Versions before 4.0 `willdurand/geocoder` will still work as usual, but with the new repository. 


### Install

In 99% of the cases you do **not** want to install this package directly. You are more likely to install one provider. 
Have a look at [the documentation](https://github.com/geocoder-php/Geocoder) to see the different providers. 

```bash
composer require willdurand/geocoder
```

### Contribute

Contributions are very welcome! Send a pull request to the [main repository](https://github.com/geocoder-php/Geocoder) or 
report any issues you find on the [issue tracker](https://github.com/geocoder-php/Geocoder/issues).
