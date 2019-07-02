<?php


namespace Geocoder\Query;


use Geocoder\Exception\InvalidArgument;

class LookupQuery implements Query
{

    /**
     * @var mixed $id
     */
    protected $id;

    /**
     * @param mixed $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $locale
     *
     * @return Query
     */
    public function withLocale(string $locale)
    {
        throw new InvalidArgument('Lookup query does not support this option');
    }

    /**
     * @param int $limit
     *
     * @return Query
     */
    public function withLimit(int $limit)
    {
        throw new InvalidArgument('Lookup query does not support this option');
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return Query
     */
    public function withData(string $name, $value)
    {
        throw new InvalidArgument('Lookup query does not support this option');
    }

    /**
     * @return string|null
     */
    public function getLocale()
    {
        throw new InvalidArgument('Lookup query does not support this option');
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        throw new InvalidArgument('Lookup query does not support this option');
    }

    /**
     * @param string $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getData(string $name, $default = null)
    {
        return $name === 'id' ? $this->id : $default;
    }

    /**
     * @return array
     */
    public function getAllData(): array
    {
        return ['id' => $this->id];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }
}