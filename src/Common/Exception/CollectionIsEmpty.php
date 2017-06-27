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
 * When you are trying to access an element on en empty collection.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CollectionIsEmpty extends \LogicException implements Exception
{
}
