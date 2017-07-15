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

use Geocoder\Plugin\Plugin;
use Geocoder\Query\Query;

/**
 * Add arbitrary data to a query
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class QueryDataPlugin implements Plugin
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $force;

    /**
     * @param array $data
     * @param bool  $force If true we overwrite existing values
     */
    public function __construct(array $data, $force = false)
    {
        $this->data = $data;
        $this->force = $force;
    }

    /**
     * {@inheritdoc}
     */
    public function handleQuery(Query $query, callable $next, callable $first)
    {
        $queryData = $query->getAllData();
        foreach ($this->data as $key => $value) {
            if ($this->force || !array_key_exists($key, $queryData)) {
                $query = $query->withData($key, $value);
            }
        }

        return $next($query);
    }
}
