<?php

declare(strict_types=1);

namespace Geocoder\Plugin\Plugin;

use Geocoder\Plugin\Plugin;
use Geocoder\Query\Query;

/**
 * Add locale on the query
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class LocalePlugin implements Plugin
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $locale
     */
    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function handleQuery(Query $query, callable $next, callable $first)
    {
        if (empty($query->getLocale())) {
            $query = $query->withLocale($this->locale);
        }

        return $next($query);
    }
}
