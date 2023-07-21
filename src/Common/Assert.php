<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Exception\InvalidArgument;

class Assert
{
    public static function latitude(mixed $value, string $message = ''): void
    {
        self::float($value, $message);
        if ($value < -90 || $value > 90) {
            throw new InvalidArgument(sprintf($message ?: 'Latitude should be between -90 and 90. Got: %s', $value));
        }
    }

    public static function longitude(mixed $value, string $message = ''): void
    {
        self::float($value, $message);
        if ($value < -180 || $value > 180) {
            throw new InvalidArgument(sprintf($message ?: 'Longitude should be between -180 and 180. Got: %s', $value));
        }
    }

    public static function notNull(mixed $value, string $message = ''): void
    {
        if (null === $value) {
            throw new InvalidArgument(sprintf($message ?: 'Value cannot be null'));
        }
    }

    private static function typeToString(mixed $value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    private static function float(mixed $value, string $message): void
    {
        if (!is_float($value)) {
            throw new InvalidArgument(sprintf($message ?: 'Expected a float. Got: %s', self::typeToString($value)));
        }
    }
}
