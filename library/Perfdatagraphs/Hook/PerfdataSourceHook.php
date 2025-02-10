<?php

namespace Icinga\Module\Perfdatagraphs\Hook;

use Icinga\Module\Perfdatagraphs\Model\PerfdataRequest;
use Icinga\Module\Perfdatagraphs\Model\PerfdataResponse;

/**
 * The PerfdataSourceHook must be implemented by a specific Performance data
 * backend.
 */
abstract class PerfdataSourceHook
{
    /**
     * getName returns the name of the hook implementation.
     * This is used to display it in the configuration.

     * @return string
     */
    abstract public function getName(): string;

    /**
     * fetchData returns an PerfdataResponse containing the perfdata.
     *
     * @param PerfdataRequest $req The request for fetching the data
     * @return PerfdataResponse
     */
    abstract public function fetchData(PerfdataRequest $req): PerfdataResponse;
}
