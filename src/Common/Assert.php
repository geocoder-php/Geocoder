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
    /**
     * @param float  $value
     * @param string $message
     */
    public static function latitude($value, $message = '')
    {
        if (!is_float($value)) {
            throw new InvalidArgument(
                sprintf($message ?: 'Expected a double. Got: %s', self::typeToString($value))
            );
        }

        if ($value < -90 || $value > 90) {
            throw new InvalidArgument(
                sprintf($message ?: 'Latitude should be between -90 and 90. Got: %s', $value)
            );
        }
    }

    /**
     * @param float  $value
     * @param string $message
     */
    public static function longitude($value, $message = '')
    {
        if (!is_float($value)) {
            throw new InvalidArgument(
                sprintf($message ?: 'Expected a doable. Got: %s', self::typeToString($value))
            );
        }

        if ($value < -180 || $value > 180) {
            throw new InvalidArgument(
                sprintf($message ?: 'Latitude should be between -90 and 90. Got: %s', $value)
            );
        }
    }

    /**
     * @param mixed  $value
     * @param string $message
     */
    public static function notNull($value, $message = '')
    {
        if (null === $value) {
            throw new InvalidArgument(sprintf($message ?: 'Value cannot be null'));
        }
    }

    private static function typeToString($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
