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

use Geocoder\Model\Bounds;
use Geocoder\Plugin\Plugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;

/**
 * Add bounds to each GeocoderQuery
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BoundsPlugin implements Plugin
{
    /**
     * @var Bounds
     */
    private $bounds;

    /**
     * @param Bounds $bounds
     */
    public function __construct(Bounds $bounds)
    {
        $this->bounds = $bounds;
    }

    /**
     * {@inheritdoc}
     */
    public function handleQuery(Query $query, callable $next, callable $first)
    {
        if (!$query instanceof GeocodeQuery) {
            return $next($query);
        }

        if (empty($query->getBounds())) {
            $query = $query->withBounds($this->bounds);
        }

        return $next($query);
    }
}
