<?php

namespace Icinga\Module\Perfdatagraphs\Model;

use JsonSerializable;

class PerfdataResponse implements JsonSerializable
{
    protected array $data;

    protected array $errors;

    public function addError(string $e): void
    {
        $this->errors[] = $e;
    }

    public function hasErrors(): bool
    {
        if (count($this->errors) > 0) {
            return true;
        }
        return false;
    }

    public function addDataset(PerfdataSet $ds): void
    {
        $this->data[] = $ds;
    }

    public function jsonSerialize(): mixed
    {
        $d = [];

        if (isset($this->errors)) {
            $d['errors'] = $this->errors;
        }
        if (isset($this->data)) {
            $d['data'] = $this->data;
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
