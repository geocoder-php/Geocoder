<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\Formatter;

use Geocoder\Formatter\StringFormatter;
use Geocoder\Model\Address;
use PHPUnit\Framework\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class StringFormatterTest extends TestCase
{
    /**
     * @var StringFormatter
     */
    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new StringFormatter();
    }

    /**
     * @dataProvider dataProviderForTestFormat
     *
     * @param array<string, mixed> $data
     */
    public function testFormat(array $data, string $format, string $expected): void
    {
        $address = Address::createFromArray($data);
        $result = $this->formatter->format($address, $format);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array<array<array<string, array<array<string, int|string>>|string>|string>>
     */
    public function dataProviderForTestFormat(): array
    {
        return [
            [
                ['streetNumber' => '10'],
                '%n',
                '10',
            ],
            [
                ['streetName' => 'Via San Marco'],
                '%S',
                'Via San Marco',
            ],
            [
                ['locality' => 'Zuerich'],
                '%L',
                'Zuerich',
            ],
            [
                ['postalCode' => '8001'],
                '%z',
                '8001',
            ],
            [
                ['adminLevels' => [['name' => 'Collin County', 'level' => 2]]],
                '%A2',
                'Collin County',
            ],
            [
                ['adminLevels' => [['code' => 'FC', 'level' => 2]]],
                '%a2',
                'FC',
            ],
            [
                ['adminLevels' => [['name' => 'Auvergne', 'level' => 1]]],
                '%A1',
                'Auvergne',
            ],
            [
                ['adminLevels' => [['code' => 'CA', 'level' => 1]]],
                '%a1',
                'CA',
            ],
            [
                ['country' => 'France'],
                '%C',
                'France',
            ],
            [
                ['countryCode' => 'fr'],
                '%c',
                'FR',
            ],
            [
                ['timezone' => 'Europe/Paris'],
                '%T',
                'Europe/Paris',
            ],
            [
                ['subLocality' => 'District'],
                '%D',
                'District',
            ],
            [
                [
                    'streetNumber' => '120',
                    'streetName' => 'Badenerstrasse',
                    'postalCode' => '8001',
                    'locality' => 'Zuerich',
                ],
                '%S %n, %z %L',
                'Badenerstrasse 120, 8001 Zuerich',
            ],
            [
                [
                    'streetNumber' => '120',
                    'streetName' => 'Badenerstrasse',
                    'postalCode' => '8001',
                    'locality' => 'Zuerich',
                ],
                '<p>%S %n, %z <a href="#%L">%L</a></p>',
                '<p>Badenerstrasse 120, 8001 <a href="#Zuerich">Zuerich</a></p>',
            ],
            [
                [
                    'streetNumber' => '120',
                    'streetName' => 'Badenerstrasse',
                    'postalCode' => '8001',
                    'locality' => 'Zuerich',
                ],
                '<p>%S %n, %z <a href="#%L">%L</a></p><p>%A2</p>',
                '<p>Badenerstrasse 120, 8001 <a href="#Zuerich">Zuerich</a></p><p></p>',
            ],
        ];
    }
}
