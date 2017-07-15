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
 * When a required PHP extension is missing.
 *
 * @author Antoine Corcy <contact@sbin.dk>
 */
final class ExtensionNotLoaded extends \RuntimeException implements Exception
{
}
