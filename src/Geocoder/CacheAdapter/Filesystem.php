<?php

namespace Geocoder\CacheAdapter;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
 
class Filesystem implements CacheInterface
{
    protected $path;

    public function __construct($path)
    {
        $path = realpath($path);
        if ( ! is_dir($path) ) {
            throw new \RuntimeException(sprintf('The directory %s does not exist', $path));
        }

        if ( ! is_writable($path) || ! is_readable($path) ) {
            throw new \RuntimeException(sprintf('The directory %s is not writeable and not readable', $path));
        }

        $this->path = $path;
    }

    /**
     * Stores a value with a unique key.
     *
     * @param string $key   A unique key.
     * @param \Geocoder\Result\ResultInterface  A result object.
     */
    public function store($key, $value)
    {
        $filename = $this->computeFilename($key);
        $dir      = $this->path . '/' . dirname($filename);

        return file_put_contents($this->path . '/' . $filename, serialize($value));
    }

    /**
     * Retrieves a value identified by its key.
     *
     * @return \Geocoder\Result\ResultInterface A result object.
     */
    public function retrieve($key)
    {
        $path = $this->path . '/' . $this->computeFilename($key);

        if ( ! is_file($path) ) {
            return null;
        }

        return unserialize(
            file_get_contents($path)
        );
    }

    protected function computeFilename($key)
    {
        $hash = sha1($key);
        return $hash . '.cache';
    }
}
