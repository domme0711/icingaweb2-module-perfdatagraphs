<?php

namespace Icinga\Module\Perfdatagraphs\Model;

use JsonSerializable;

/**
 * PerfdataSeries represents a single series (y-axis) on the chart.
 */
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

    public function getName(): string
    {
        return $this->name;
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

    /**
     * isEmpty checks if this series contains data and if the data is not null
     */
    public function isEmpty(): bool
    {
        if (count($this->values) === 0) {
            return true;
        }

        // Keeping it simply since values are an iterable (e.g. SplFixedArray)
        foreach ($this->values as $v) {
            if (!is_null($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * isValid checks if this series contains valid data
     */
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
