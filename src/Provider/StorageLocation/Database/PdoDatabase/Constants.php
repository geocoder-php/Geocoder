<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation\Database\PdoDatabase;

use Geocoder\Model\Address;
use Geocoder\Model\AdminLevel;
use Geocoder\Model\Bounds;
use Geocoder\Model\Coordinates;
use Geocoder\Model\Country;
use Geocoder\Provider\StorageLocation\Model\Place;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
final class Constants
{
    const OBJECT_HASH = 'object_hash';

    const COMPRESSED_DATA = 'compressed_data';

    const LOCALE = 'locale';

    const PROVIDED_BY = 'provided_by';

    const COORDINATE_LATITUDE = 'coordinates_latitude';

    const COORDINATE_LONGITUDE = 'coordinates_longitude';

    const BOUNDS_WEST = 'bounds_west';

    const BOUNDS_SOUTH = 'bounds_south';

    const BOUNDS_NORTH = 'bounds_north';

    const BOUNDS_EAST = 'bounds_east';

    const STREET_NUMBER = 'street_number';

    const STREET_NAME = 'street_name';

    const POSTAL_CODE = 'postal_code';

    const LOCALITY = 'locality';

    const SUB_LOCALITY = 'sub_locality';

    const COUNTRY_CODE = 'country_code';

    const COUNTY_NAME = 'country_name';

    const TIMEZONE = 'timezone';

    const LEVEL = 'level';

    const NAME = 'name';

    const CODE = 'code';

    const POLYGON_NUMBER = 'polygon_number';

    const POINT_NUMBER = 'point_number';

    const LATITUDE = 'latitude';

    const LONGITUDE = 'longitude';

    const SEARCH_TEXT = 'search_text';

    const FIELDS_FOR_PLACE = [
        self::OBJECT_HASH => Place::class.'::objectHash',
        self::COMPRESSED_DATA => '',
    ];

    const FIELDS_FOR_ADDRESS = [
        self::OBJECT_HASH => Place::class.'::objectHash',
        self::LOCALE => Address::class.'::locale',
        self::PROVIDED_BY => Address::class.'::providedBy',
        self::COORDINATE_LATITUDE => Coordinates::class.'::latitude',
        self::COORDINATE_LONGITUDE => Coordinates::class.'::longitude',
        self::BOUNDS_WEST => Bounds::class.'::west',
        self::BOUNDS_SOUTH => Bounds::class.'::south',
        self::BOUNDS_NORTH => Bounds::class.'::north',
        self::BOUNDS_EAST => Bounds::class.'::east',
        self::STREET_NUMBER => Address::class.'::streetNumber',
        self::STREET_NAME => Address::class.'::streetName',
        self::POSTAL_CODE => Address::class.'::postalCode',
        self::LOCALITY => Address::class.'::locality',
        self::SUB_LOCALITY => Address::class.'::subLocality',
        self::COUNTRY_CODE => Country::class.'::code',
        self::COUNTY_NAME => Country::class.'::name',
        self::TIMEZONE => Address::class.'::timezone'
    ];

    const FIELDS_FOR_ADMIN_LEVEL = [
        self::OBJECT_HASH => Place::class.'::objectHash',
        self::LOCALE => Address::class.'::locale',
        self::LEVEL => AdminLevel::class.'::level',
        self::NAME => AdminLevel::class.'::name',
        self::CODE => AdminLevel::class.'::code',
    ];

    const FIELDS_FOR_POLYGON = [
        self::OBJECT_HASH => Place::class.'::objectHash',
        self::POLYGON_NUMBER => '',
        self::POINT_NUMBER => '',
        self::LATITUDE => Coordinates::class.'::latitude',
        self::LONGITUDE => Coordinates::class.'::longitude',
    ];

    const FIELDS_FOR_ACTUAL_KEYS = [
        self::OBJECT_HASH => Place::class.'::objectHash',
        self::LOCALE => Address::class.'::locale',
        self::SEARCH_TEXT => '',
    ];
}
