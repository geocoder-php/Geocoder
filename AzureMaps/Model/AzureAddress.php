<?php
/**
 * Created by PhpStorm.
 * User: Max Langerman
 * Date: 7/13/20
 * Time: 12:10 AM
 */
namespace Geocoder\Model;

/**
 * @author Max Langerman <max@langerman.io>
 * */
class AzureAddress extends Address
{
    /**
     * @var string
     * */
    private $id;

    /**
     * @var string
     * */
    private $type;

    /**
     * @var string
     * */
    private $score;
}
