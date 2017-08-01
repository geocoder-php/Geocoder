<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin\Plugin;

use Geocoder\Collection;
use Geocoder\Exception\Exception;
use Geocoder\Query\Query;
use Psr\Log\LoggerInterface;

/**
 * Log all queries and the result/failure
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class LoggerPlugin
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handleQuery(Query $query, callable $next, callable $first)
    {
        $startTime = microtime(true);
        $logger = $this->logger;

        return $next($query)->then(function (Collection $result) use ($logger, $query, $startTime) {
            $duration = (microtime(true) - $startTime) * 1000;
            $this->logger->info(sprintf('[Geocoder] Got %d results in %0.2f ms for query %s', count($result), $duration, $query->__toString()));

            return $result;
        }, function (Exception $exception) use ($logger, $query, $startTime) {
            $duration = (microtime(true) - $startTime) * 1000;
            $this->logger->error(sprintf('[Geocoder] Failed with %s after %0.2f ms for query %s', get_class($exception), $duration, $query->__toString()));

            throw $exception;
        });
    }
}
