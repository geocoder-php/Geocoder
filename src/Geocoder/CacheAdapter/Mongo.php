<?php

namespace Geocoder\CacheAdapter;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class Mongo implements CacheInterface {

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
     * Get the collectioname
     *
     * @return \MongoCollection
     */
    public function getCollection()
    {
        return $this->mongo
                ->selectDB($this->databaseName)
                ->selectCollection($this->collectionName);
    }

    /**
     * Stores a value with a unique key.
     *
     * @param string $key   A unique key.
     * @param \Geocoder\Result\ResultInterface  A result object.
     */
    public function store($key, $value)
    {
        $data = array('key' => $key, 'data' => $value);
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
        if ( null === $data ) {
            return null;
        }
        return $data['data'];
    }
}
