<?php

namespace Icinga\Module\Perfdatagraphs\Model;

use JsonSerializable;

class PerfdataSet implements JsonSerializable
{
     /** @var string The title of this dataset */
    protected string $title;

     /** @var string The unit of this dataset */
    protected string $unit;

     /** @var string The fill of this dataset */
    protected string $fill;

     /** @var string The stroke of this dataset */
    protected string $stroke;

    /** @var iterable The timstamps for this dataset */
    protected iterable $timestamps;

    /** @var array List of PerfdataSeries for this dataset */
    protected array $series;

    /**
     * @param string $title
     * @param string $unit
     */
    public function __construct(string $title, string $unit = '')
    {
        $this->title = $title;
        $this->unit = $unit;
    }

    public function jsonSerialize(): mixed
    {
        $d = [];

        if (isset($this->title)) {
            $d['title'] = $this->title;
        }

        if (isset($this->unit)) {
            $d['unit'] = $this->unit;
        }

        if (isset($this->fill)) {
            $d['fill'] = $this->fill;
        }

        if (isset($this->stroke)) {
            $d['stroke'] = $this->stroke;
        }

        if (isset($this->timestamps)) {
            $d['timestamps'] = $this->timestamps;
        }

        if (isset($this->series)) {
            $d['series'] = $this->series;
        }
        return $d;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setUnit(string $u): void
    {
        $this->unit = $u;
    }

    public function setFill(string $f): void
    {
        $this->fill = $f;
    }

    public function setStroke(string $s): void
    {
        $this->stroke = $s;
    }

    public function addSeries(PerfdataSeries $s): void
    {
        $this->series[] = $s;
    }

    public function setTimestamps(iterable $ts): void
    {
        $this->timestamps = $ts;
    }
}
