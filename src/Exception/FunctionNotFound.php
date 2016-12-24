<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Exception;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FunctionNotFound extends \RuntimeException implements Exception
{
    /**
     * @param string $functionName
     * @param string $description
     */
    public function __construct($functionName, $description = null)
    {
        parent::__construct(sprintf('The function "%s" cannot be found.%s',
            $functionName,
            null !== $description ? sprintf(' %s', $description) : ''
        ));
    }
}
