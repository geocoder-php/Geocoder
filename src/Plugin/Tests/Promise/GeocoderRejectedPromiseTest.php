<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin\Tests\Promise;

use Geocoder\Collection;
use Geocoder\Exception\Exception;
use Geocoder\Exception\LogicException;
use Geocoder\Plugin\Promise\GeocoderFulfilledPromise;
use Geocoder\Plugin\Promise\GeocoderRejectedPromise;
use Http\Promise\Promise;
use PHPUnit\Framework\TestCase;

class GeocoderRejectedPromiseTest extends TestCase
{
    public function testItReturnsAFulfilledPromise(): void
    {
        $exception = $this->createStub(Exception::class);
        $promise = new GeocoderRejectedPromise($exception);
        $collection = $this->createStub(Collection::class);

        $returnPromise = $promise->then(null, function () use ($collection) {
            return $collection;
        });

        $this->assertInstanceOf(GeocoderFulfilledPromise::class, $returnPromise);
        $this->assertSame(Promise::FULFILLED, $returnPromise->getState());
        $this->assertSame($collection, $returnPromise->wait());
    }

    public function testItReturnsARejectedPromise(): void
    {
        $collection = $this->createStub(Exception::class);
        $promise = new GeocoderRejectedPromise($collection);

        $returnPromise = $promise->then(null, function () {
            throw new LogicException();
        });

        $this->assertInstanceOf(GeocoderRejectedPromise::class, $returnPromise);
        $this->assertSame(Promise::REJECTED, $returnPromise->getState());

        $this->expectException(Exception::class);
        $returnPromise->wait();
    }

    public function testItReturnsItselfWhenNoOnRejectedCallbackIsPassed(): void
    {
        $collection = $this->createStub(Exception::class);
        $promise = new GeocoderRejectedPromise($collection);

        $returnPromise = $promise->then();
        $this->assertSame($promise, $returnPromise);
    }
}
