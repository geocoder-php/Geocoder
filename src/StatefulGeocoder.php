<?php

namespace Geocoder;

use Geocoder\Model\AddressCollection;
use Geocoder\Model\Bounds;
use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;

/**
 *
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class StatefulGeocoder implements Geocoder, LocaleAwareGeocoder
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var Bounds
     */
    private $bounds;

    /**
     * @var int
     */
    private $limit = Provider::MAX_RESULTS;

    /**
     * @var Provider
     */
    private $provider;

    /**
     *
     * @param Provider $provider
     * @param string $locale
     */
    public function __construct(Provider $provider, $locale = null)
    {
        $this->provider = $provider;
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function geocode($value)
    {
        $query = GeocodeQuery::create($value)
            ->withLimit($this->limit);

        if (!empty($this->locale)) {
            $query->withLocale($this->locale);
        }

        if (!empty($this->bouds)) {
            $query->withBounds($this->bounds);
        }

        return $this->provider->geocodeQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function reverse($latitude, $longitude)
    {
        $query = ReverseQuery::fromCoordinates($latitude, $longitude)
            ->withLimit($this->limit);

        if (!empty($this->locale)) {
            $query->withLocale($this->locale);
        }

        return $this->provider->reverseQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $data = $query->getLocale();
        if (empty($data)) {
            $query->withLocale($this->locale);
        }

        $data = $query->getBounds();
        if (empty($data)) {
            $query->withBounds($this->bounds);
        }

        $this->provider->geocodeQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        $data = $query->getLocale();
        if (empty($data)) {
            $query->withLocale($this->locale);
        }

        $this->provider->reverseQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'stateful_geocoder';
    }
}
