<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Formatter;

use Geocoder\Result\ResultInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Formatter implements FormatterInterface
{
    /**
     * @var ResultInterface
     */
    private $result;

    /**
     * @param ResultInterface $result
     */
    public function __construct(ResultInterface $result)
    {
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function format($format)
    {
        return strtr($format, array(
            FormatterInterface::STREET_NUMBER   => $this->result->getStreetNumber(),
            FormatterInterface::STREET_NAME     => $this->result->getStreetName(),
            FormatterInterface::CITY            => $this->result->getCity(),
            FormatterInterface::ZIPCODE         => $this->result->getZipcode(),
            FormatterInterface::CITY_DISTRICT   => $this->result->getCityDistrict(),
            FormatterInterface::COUNTY          => $this->result->getCounty(),
            FormatterInterface::COUNTY_CODE     => $this->result->getCountyCode(),
            FormatterInterface::REGION          => $this->result->getRegion(),
            FormatterInterface::REGION_CODE     => $this->result->getRegionCode(),
            FormatterInterface::COUNTRY         => $this->result->getCountry(),
            FormatterInterface::COUNTRY_CODE    => $this->result->getCountryCode(),
            FormatterInterface::TIMEZONE        => $this->result->getTimezone(),
        ));
    }
}
