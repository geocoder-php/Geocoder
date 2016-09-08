<?php
namespace Geocoder\Model;


/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface CountryInterface
{
    /**
     * Returns the country name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the country ISO code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Returns a string with the country name.
     *
     * @return string
     */
    public function __toString();
}