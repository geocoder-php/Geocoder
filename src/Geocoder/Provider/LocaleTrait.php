<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

/**
 * @author Premi Giorgio <giosh94mhz@gmail.com>
 */
trait LocaleTrait
{
    /**
     * @var string|null
     */
    private $locale;

    /**
     * Return the locale to be used in locale aware requests.
     *
     * In case there is no locale in use, null is returned.
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the locale to be used.
     *
     * @param string|null
     *
     * @return LocaleAwareProvider
     */
    public function setLocale($locale)
    {
        $this->locale = $locale ?: null;

        return $this;
    }
}
