<?php

declare(strict_types=1);

namespace Geocoder\Provider\Faker;

use Faker\Factory;
use Geocoder\Collection;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;

/**
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
final class Faker implements Provider
{
    public const PROVIDER_NAME = 'faker';

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return $this->generateFakeLocations($query);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        return $this->generateFakeLocations($query);
    }

    public function getName(): string
    {
        return self::PROVIDER_NAME;
    }

    private function generateFakeLocations(Query $query): Collection
    {
        $faker = Factory::create($query->getLocale() ?? Factory::DEFAULT_LOCALE);

        $results = [];

        $i = 0;
        while ($i < $query->getLimit()) {
            $builder = new AddressBuilder($this->getName());
            $builder
                ->setCoordinates($faker->latitude(), $faker->longitude())
                ->setStreetNumber($faker->buildingNumber())
                ->setStreetName($faker->streetName())
                ->setPostalCode($faker->postcode())
                ->setLocality($faker->city())
                ->setCountry($faker->country())
                ->setCountryCode($faker->countryCode())
                ->setTimezone($faker->timezone())
            ;

            $results[] = $builder->build();
            ++$i;
        }

        return new AddressCollection($results);
    }
}
