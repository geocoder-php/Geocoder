<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Exception;

/**
 * Thrown when you no longer may access the API because your quota has exceeded.
 *
 * @author Max V. Kovrigovich <mvk@tut.by>
 */
final class QuotaExceeded extends \RuntimeException implements Exception
{
}
