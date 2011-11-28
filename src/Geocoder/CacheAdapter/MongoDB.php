<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\CacheAdapter;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class MongoDB implements CacheInterface
{
    /**
     * @var \Mongo
     */
    private $mongo;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $collectionName;

    public function __construct(\Mongo $mongo, $databaseName = 'geocoder', $collectionName = 'cache')
    {
        $this->mongo = $mongo;
        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;
    }

    /**
     * Stores a value with a unique key.
     *
     * @param string $key   A unique key.
     * @param \Geocoder\Result\ResultInterface  A result object.
     */
    public function store($key, $value)
    {
        $data = array('key' => $key, 'data' => serialize($value));
        $this->getCollection()->insert($data);
    }

    /**
     * Retrieves a value identified by its key.
     *
     * @return \Geocoder\Result\ResultInterface A result object.
     */
    public function retrieve($key)
    {
        $data = $this->getCollection()->findOne(array('key' => $key));
        if (null === $data) {
            return null;
        }

        return unserialize($data['data']);
    }

    /**
     * Get the collectioname
     *
     * @return \MongoCollection
     */
    protected function getCollection()
    {
        return $this->mongo
                ->selectDB($this->databaseName)
                ->selectCollection($this->collectionName);
    }
}
