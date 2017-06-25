<?php

namespace Geocoder\Tests;

use Geocoder\Query\GeocodeQuery;
use PHPUnit\Framework\TestCase;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class GeocodeQueryTest extends TestCase
{
    public function testToString()
    {
        $query = GeocodeQuery::create('foo');
        $query = $query->withLocale('en');
        $query = $query->withLimit(3);
        $query = $query->withData('name', 'value');

        $string = $query->__toString();
        $this->assertContains('GeocodeQuery', $string);
        $this->assertContains('"text":"foo"', $string);
        $this->assertContains('"locale":"en"', $string);
        $this->assertContains('"limit":3', $string);
        $this->assertContains('"name":"value"', $string);
    }
}
