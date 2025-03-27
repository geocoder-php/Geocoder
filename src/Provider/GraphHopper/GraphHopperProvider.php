<?php

declare(strict_types=1);

namespace Geocoder\Provider\GraphHopper;

enum GraphHopperProvider: string
{
    case Default = 'default';
    case Nominatim = 'nominatim';
    case Gisgraphy = 'gisgraphy';
    case Nettoolkit = 'nettoolkit';
    case Opencagedata = 'opencagedata';
}
