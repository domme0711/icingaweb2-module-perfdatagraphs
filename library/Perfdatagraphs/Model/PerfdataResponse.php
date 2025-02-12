<?php

namespace Icinga\Module\Perfdatagraphs\Model;

use JsonSerializable;

/**
 * PerfdataResponse is what the PerfdataSourceHook returns
 * and what we pass to the module.js
 */
class PerfdataResponse implements JsonSerializable
{
    protected array $data = [];

    protected array $errors = [];

    /**
     * addError adds an error message to this object.
     * @param string $e error message to append
     */
    public function addError(string $e): void
    {
        $this->errors[] = $e;
    }

    /**
     * hasErrors checks if this response has any errors.
     */
    public function hasErrors(): bool
    {
        if (count($this->errors) > 0) {
            return true;
        }
        return false;
    }

    /**
     * isValid checks if this response contains data
     */
    public function isEmpty(): bool
    {
        return count($this->data) === 0;
    }

    /**
     * isValid checks if this response contains valid data
     */
    public function isValid(): bool
    {
        foreach ($this->data as $dataset) {
            if (!$dataset->isValid()) {
                return false;
            }
        }

        return true;
    }

    /**
     * getDataset returns a dataset by its name.
     *
     * @param string $title the dataset to return
     * @return PerfdataSet
     */
    public function getDataset(string $title): ?PerfdataSet
    {
        if (array_key_exists($title, $this->data)) {
            return $this->data[$title];
        }
    }

    /**
     * addDataset adds a new PerfdataSet (which respresents a single chart in the frontend).
     * @param PerfdataSet $ds the dataset to add
     */
    public function addDataset(PerfdataSet $ds): void
    {
        $this->data[$ds->getTitle()] = $ds;
    }

    public function jsonSerialize(): mixed
    {
        $d = [];

        if (isset($this->errors)) {
            $d['errors'] = $this->errors;
        }
        if (isset($this->data)) {
            $d['data'] = array_values($this->data);
        }

        return $d;
    }

    /**
     * mergeCustomVars merges the performance data with the custom vars,
     * so that each series receives its corresponding vars.
     * CustomVars override data in the PerfdataSet.
     *
     * We could have also done this browser-side but decided to do this here
     * because of simpler testability. We could change that if browser-side merging
     * is more performant.
     *
     * If the functionality remains here, we should optimize if for performance.
     *
     * @param array $customvars The custom variables for the given object
     */
    public function mergeCustomVars(array $customvars): void
    {
        // If we don't have any custom vars simply return
        if (empty($customvars)) {
            return;
        }

        // If we don't have any data simply return
        if (empty($this->data)) {
            return;
        }

        foreach ($this->data as $dkey => $dataset) {
            $title = $dataset->getTitle();
            if (array_key_exists($dataset->getTitle(), $customvars)) {
                if (isset($customvars[$title]['unit'])) {
                    $this->data[$dkey]->setUnit($customvars[$title]['unit']);
                }
                if (isset($customvars[$title]['fill'])) {
                    $this->data[$dkey]->setFill($customvars[$title]['fill']);
                }
                if (isset($customvars[$title]['stroke'])) {
                    $this->data[$dkey]->setStroke($customvars[$title]['stroke']);
                }
            }
        }
    }
}
