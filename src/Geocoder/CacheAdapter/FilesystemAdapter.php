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
class FilesystemAdapter implements CacheInterface
{
    /**
     * @var string
     */
    protected $path;

    public function __construct($path)
    {
        $path = realpath($path);
        if (! is_dir($path)) {
            throw new \RuntimeException(sprintf('The directory %s does not exist', $path));
        }

        if (! is_writable($path) || ! is_readable($path)) {
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
        $dir      = $this->path . DIRECTORY_SEPARATOR . dirname($filename);

        return file_put_contents($this->path . DIRECTORY_SEPARATOR . $filename, serialize($value));
    }

    /**
     * Retrieves a value identified by its key.
     *
     * @return \Geocoder\Result\ResultInterface A result object.
     */
    public function retrieve($key)
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $this->computeFilename($key);

        if (! is_file($path)) {
            return null;
        }

        return unserialize(
            file_get_contents($path)
        );
    }

    /**
     * Compute the filename for the given key
     *
     * @param $key
     * @return string
     */
    protected function computeFilename($key)
    {
        return sha1($key) . '.cache';
    }
}
