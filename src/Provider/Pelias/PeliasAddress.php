<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias;

use Geocoder\Model\Address;

final class PeliasAddress extends Address
{
    /**
     * @var string|null
     */
    private $gid;

    /**
     * @var string|null
     */
    private $source;

    /**
     * @return string|null
     */
    public function getGID(): ?string
    {
        return $this->gid;
    }

    /**
     * @param string|null $gid
     *
     * @return PeliasAddress
     */
    public function withGID(string $gid = null): PeliasAddress
    {
        $new = clone $this;
        $new->gid = $gid;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     *
     * @return PeliasAddress
     */
    public function withSource(string $source = null): PeliasAddress
    {
        $new = clone $this;
        $new->source = $source;

        return $new;
    }
}
