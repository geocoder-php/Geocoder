<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Model\Bounds;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface BoundsAwareProvider extends Provider
{
    /**
     * Configure the provider to only return Locations within the $bounds.
     */
    public function setBounds(Bounds $bounds);
}
