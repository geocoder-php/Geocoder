<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin;

use Geocoder\Query\Query;
use Http\Promise\Promise;

/**
 * A plugin is a middleware to transform the Query and/or the Collection.
 *
 * The plugin can:
 *  - break the chain and return a Collection
 *  - dispatch the Query to the next middleware
 *  - restart the Query
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface Plugin
{
    /**
     * Handle the Query and return the Collection coming from the next callable.
     *
     * @param Query    $query
     * @param callable $next  Next middleware in the chain, the query is passed as the first argument
     * @param callable $first First middleware in the chain, used to to restart a request
     *
     * @return Promise Resolves a Collection or fails with an Geocoder\Exception\Exception
     */
    public function handleQuery(Query $query, callable $next, callable $first);
}
