<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests;

use Geocoder\Query\ReverseQuery;
use PHPUnit\Framework\TestCase;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ReverseQueryTest extends TestCase
{
    public function testToString(): void
    {
        $query = ReverseQuery::fromCoordinates(1, 2);
        $query = $query->withLocale('en');
        $query = $query->withLimit(3);
        $query = $query->withData('name', 'value');

        $string = $query->__toString();
        $this->assertStringContainsString('ReverseQuery', $string);
        $this->assertStringContainsString('"lat":1', $string);
        $this->assertStringContainsString('"lng":2', $string);
        $this->assertStringContainsString('"locale":"en"', $string);
        $this->assertStringContainsString('"limit":3', $string);
        $this->assertStringContainsString('"name":"value"', $string);
    }
}
