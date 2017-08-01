<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin\Promise;

use Geocoder\Collection;
use Geocoder\Exception\Exception;
use Http\Promise\Promise;

/**
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class GeocoderFulfilledPromise implements Promise
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onFulfilled) {
            return $this;
        }

        try {
            return new self($onFulfilled($this->collection));
        } catch (Exception $e) {
            return new GeocoderRejectedPromise($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return Promise::FULFILLED;
    }

    /**
     * {@inheritdoc}
     */
    public function wait($unwrap = true)
    {
        if ($unwrap) {
            return $this->collection;
        }
    }
}
