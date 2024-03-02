<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin\Promise;

use Geocoder\Exception\Exception;
use Http\Promise\Promise;

/**
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class GeocoderRejectedPromise implements Promise
{
    /**
     * @var Exception
     */
    private $exception;

    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): Promise
    {
        if (null === $onRejected) {
            return $this;
        }

        try {
            return new GeocoderFulfilledPromise($onRejected($this->exception));
        } catch (Exception $e) {
            return new self($e);
        }
    }

    public function getState(): string
    {
        return Promise::REJECTED;
    }

    public function wait($unwrap = true)
    {
        if ($unwrap) {
            throw $this->exception;
        }
    }
}
