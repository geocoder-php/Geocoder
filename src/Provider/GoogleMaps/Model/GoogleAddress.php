<?php

namespace Geocoder\Provider\GoogleMaps\Model;

use Geocoder\Model\Address;
use Geocoder\Model\Coordinates;

class GoogleAddress extends Address
{
    /**
     * @var string|null
     */
    private $locationType;

    /**
     * @param null|string $locationType
     *
     * @return GoogleAddress
     */
    public function setLocationType($locationType)
    {
        $this->locationType = $locationType;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLocationType()
    {
        return $this->locationType;
    }
}
