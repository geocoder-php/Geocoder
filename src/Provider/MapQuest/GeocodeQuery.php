<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MapQuest;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Formatter\StringFormatter;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery as BaseGeocodeQuery;

class GeocodeQuery extends BaseGeocodeQuery implements GetAddressInterface
{
    const DATA_KEY_ADDRESS = 'address';

    /**
     * The address or text that should be geocoded.
     *
     * @var string
     */
    protected $text;

    /**
     * @var array
     */
    protected $data = [];

    protected function __construct($address)
    {
        if ($address instanceof Location) {
            $this->data[static::DATA_KEY_ADDRESS] = $address;
        } else {
            if (empty($address)) {
                throw new InvalidArgument('Geocode query cannot be empty');
            }
            $this->text = $address;
        }
    }

    public static function create(string $text): BaseGeocodeQuery
    {
        return new self($text);
    }

    public static function createFromAddress(Location $address): self
    {
        return new self($address);
    }

    public function withText(string $text): BaseGeocodeQuery
    {
        $new = clone $this;
        $new->text = $text;

        return $new;
    }

    public function withData(string $name, $value): BaseGeocodeQuery
    {
        $new = clone $this;
        $new->data[$name] = $value;

        return $new;
    }

    public function getAddress()
    {
        return $this->getData(static::DATA_KEY_ADDRESS);
    }

    public function getText(): string
    {
        if (!$this->text) {
            $address = $this->getAddress();
            if ($address instanceof Location) {
                $this->text = $this->formatAddress($address);
            }
        }

        return $this->text;
    }

    public function getData(string $name, $default = null)
    {
        if (!array_key_exists($name, $this->data)) {
            return $default;
        }

        return $this->data[$name];
    }

    public function getAllData(): array
    {
        return $this->data;
    }

    protected function formatAddress(Location $address): string
    {
        $formatter = new StringFormatter();

        return trim($formatter->format($address, '%n %S, %L, %a1 %z, %C'));
    }
}
