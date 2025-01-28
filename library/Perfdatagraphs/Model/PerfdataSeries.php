<?php

namespace Icinga\Module\Perfdatagraphs\Model;

use JsonSerializable;

class PerfdataSeries implements JsonSerializable
{
     /** @var string The name for this series */
    protected string $name;

     /** @var iterable The values for this series */
    protected iterable $values = [];

    /**
     * @param string $name
     * @param iterable $values
     */
    public function __construct(string $name, iterable $values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    public function jsonSerialize(): mixed
    {
        $d = [];

        if (isset($this->name)) {
            $d['name'] = $this->name;
        }

        if (isset($this->values)) {
            $d['values'] = $this->values;
        }

        return $d;
    }

    public function isValid(): bool
    {
        if (empty($this->name)) {
            return false;
        }

        if (count($this->values) === 0) {
            return false;
        }

        return true;
    }
}
