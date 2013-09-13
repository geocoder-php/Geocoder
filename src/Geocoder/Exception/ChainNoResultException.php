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
     * Constructor
     * 
     * @param string $message 
     * @param array $exceptions Array of Exception instances
     * @access public
     * @return void
     */
    public function __construct($message = "", array $exceptions = array())
    {
        parent::__construct($message);
        $this->setExceptions($exceptions);
    }

    /**
     * exceptions 
     * 
     * @var array
     * @access private
     */
    private $exceptions = array();

    /**
     * Get the exceptions
     * 
     * @access public
     * @return void
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

    /**
     * Set the exceptions
     * 
     * @param array $exceptions Array of Exception instances
     * @access public
     * @return void
     */
    public function setExceptions(array $exceptions)
    {
        foreach ($exceptions as $exception) {
            $this->addException($exception);
        }
    }

    /**
     * Add an exception
     * 
     * @param Exception $exception 
     * @access public
     * @return void
     */
    public function addException(\Exception $exception)
    {
        $this->exceptions[] = $exception;
    }

}
