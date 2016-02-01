<?php

namespace Geocoder\CacheStrategy;

interface Strategy
{
    function invoke($key, callable $function);
}
