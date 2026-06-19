<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpApi\Model;

use Geocoder\Model\Address;

final class IpApiLocation extends Address
{
    private string|null $currency;

    private string|null $callingCode;

    private bool $isProxy;

    private bool $isHosting;

    public function isProxy(): bool
    {
        return $this->isProxy;
    }

    public function withCurrency(string|null $currency): self
    {
        $new = clone $this;
        $new->currency = $currency;

        return $new;
    }

    public function getCurrency(): string|null
    {
        return $this->currency;
    }

    public function withCallingCode(string|null $callingCode): self
    {
        $new = clone $this;
        $new->callingCode = $callingCode;

        return $new;
    }

    public function getCallingCode(): string|null
    {
        return $this->callingCode;
    }

    public function withIsProxy(bool $isProxy): self
    {
        $new = clone $this;
        $new->isProxy = $isProxy;

        return $new;
    }

    public function isHosting(): bool
    {
        return $this->isHosting;
    }

    public function withIsHosting(bool $isHosting): self
    {
        $new = clone $this;
        $new->isHosting = $isHosting;

        return $new;
    }
}
