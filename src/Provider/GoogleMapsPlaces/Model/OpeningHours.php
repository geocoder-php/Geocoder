<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMapsPlaces\Model;

use stdClass;

/**
 * @author atymic <atymicq@gmail.com>
 */
class OpeningHours
{
    /**
     * @var bool|null
     */
    private $openNow;

    /**
     * @var array[]
     */
    private $periods;

    /**
     * @var string[]
     */
    private $weekdayText;

    /**
     * @param bool|null $openNow
     * @param array[]   $periods
     * @param string[]  $weekdayText
     */
    public function __construct($openNow, array $periods, array $weekdayText)
    {
        $this->openNow = $openNow;
        $this->periods = $periods;
        $this->weekdayText = $weekdayText;
    }

    /**
     * @return bool|null
     */
    public function isOpenNow()
    {
        return $this->openNow;
    }

    /**
     * @return array[]
     */
    public function getPeriods(): array
    {
        return $this->periods;
    }

    /**
     * @return string[]
     */
    public function getWeekdayText(): array
    {
        return $this->weekdayText;
    }

    public static function fromResult(stdClass $openingHours): self
    {
        return new self(
            $openingHours->open_now ?? null,
            $openingHours->periods ?? [],
            $openingHours->weekday_text ?? []
        );
    }
}
