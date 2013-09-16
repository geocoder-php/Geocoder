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
 * @author Ben Glassman <bglassman@gmail.com>
 */
class ChainNoResultException extends NoResultException
{
    /**
     * Exceptions from chained providers
     *
     * @var array
     */
    private $exceptions = array();

    /**
     * Constructor
     *
     * @param string $message
     * @param array  $exceptions Array of Exception instances
     */
    public function __construct($message = '', array $exceptions = array())
    {
        parent::__construct($message);

        $this->exceptions = $exceptions;
    }

    /**
     * Get the exceptions from chained providers
     *
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
}
