<?php
namespace Geocoder\Model;


/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface AdminLevelInterface
{
    /**
     * Returns the administrative level
     *
     * @return int Level number [1,5]
     */
    public function getLevel();

    /**
     * Returns the administrative level name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the administrative level short name.
     *
     * @return string
     */
    public function getCode();

    /**
     * Returns a string with the administrative level name.
     *
     * @return string
     */
    public function __toString();
}