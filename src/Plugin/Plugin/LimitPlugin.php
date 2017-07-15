<?php

declare(strict_types=1);

namespace Geocoder\Plugin\Plugin;

use Geocoder\Plugin\Plugin;
use Geocoder\Query\Query;

/**
 * Add limit on the query
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class LimitPlugin implements Plugin
{
    /**
     * @var int
     */
    private $limit;

    /**
     * @param int $limit
     */
    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function handleQuery(Query $query, callable $next, callable $first)
    {
        if (empty($query->getLocale())) {
            $query = $query->withLimit($this->limit);
        }

        return $next($query);
    }
}
