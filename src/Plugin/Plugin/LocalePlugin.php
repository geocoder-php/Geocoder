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
 * Add locale on the query.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class LocalePlugin implements Plugin
{
    /**
     * @var string
     */
    private $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function handleQuery(Query $query, callable $next, callable $first)
    {
        $locale = $query->getLocale();
        if (null === $locale || '' === $locale) {
            $query = $query->withLocale($this->locale);
        }

        return $next($query);
    }
}
