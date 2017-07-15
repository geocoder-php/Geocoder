<?php

declare(strict_types=1);

namespace Geocoder\Plugin\Exception;

use Geocoder\Exception\Exception;
use Geocoder\Query\Query;

/**
 * Thrown when the Plugin Client detects an endless loop.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
class LoopException extends \RuntimeException implements Exception
{
    /**
     * @var Query
     */
    private $query;

    public static function create($message, Query $query)
    {
        $ex = new self($message);
        $ex->query = $query;

        return $ex;
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }
}
