<?php
namespace Geocoder\Model\Query;

use Geocoder\Model\Coordinates;


/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface Query
{
    /**
     * @return int
     */
    public function getLimit();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @return array
     */
    public function getData();
}