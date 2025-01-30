<?php

namespace Icinga\Module\Perfdatagraphs\Model;

use JsonSerializable;

/**
 * PerfdataSet represents a single chart in the frontend.
 * It in turn can contain several series that are drawn on the chart.
 */
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
    protected iterable $timestamps = [];

    /** @var array List of PerfdataSeries for this dataset */
    protected array $series = [];

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

    /**
     * isValid checks if this dataset contains valid data
     */
    public function isValid(): bool
    {
        if (empty($this->title)) {
            return false;
        }

        if (count($this->timestamps) === 0) {
            return false;
        }

        if (count($this->series) === 0) {
            return false;
        }

        foreach ($this->series as $s) {
            if (!$s->isValid()) {
                return false;
            }
        }

        return true;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * setUnit sets the unit for this data series.
     * @param string $u
     */
    public function setUnit(string $u): void
    {
        $this->unit = $u;
    }

    /**
     * setFill sets the fill color of the data series.
     * @param string $s
     */
    public function setFill(string $f): void
    {
        $this->fill = $f;
    }

    /**
     * setStroke sets the stroke color of the data series.
     * @param string $s
     */
    public function setStroke(string $s): void
    {
        $this->stroke = $s;
    }

    /**
     * addSeries adds a new data series to this dataset.
     * @param PerfdataSeries $s
     */
    public function addSeries(PerfdataSeries $s): void
    {
        $this->series[] = $s;
    }

    /**
     * setTimestamps sets the timestamps for this dataset.
     * @param iterable $ts
     */
    public function setTimestamps(iterable $ts): void
    {
        $this->timestamps = $ts;
    }
}
