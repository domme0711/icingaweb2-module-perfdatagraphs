<?php

namespace Icinga\Module\Perfdatagraphs\Hook;

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
     * fetchData returns an array containing the perfdata.
     * The duration uses the ISO8601 Durations format as string,
     * so that backends can parse it and calculate its date format.
     * It represents the end of the timerange, with now() being the start.
     *
     * Icinga attributes, like host, service, checkcommand should be passed
     * as they are without modification specific to the backend. Each backend
     * can modify these if required (e.g. Graphite special characters to dots).
     *
     * Since we are going to JSON encode the response, a simple array as return
     * is probably best instead of an object.
     *
     * @param string $hostName host name for the performance data query
     * @param string $serviceName service name for the performance data query
     * @param string $checkCommand checkcommand name for the performance data query
     * @param string $duration for which to fetch the data for in PHP's DateInterval format (e.g. PT12H, P1D, P1Y)
     * @param array $metrics a list of metrics that are requested, if not set all available metrics should be returned
     * @return array
     */
    abstract public function fetchData(
        string $hostName,
        string $serviceName,
        string $checkCommand,
        string $duration,
        array $metrics
    ): array;
}
