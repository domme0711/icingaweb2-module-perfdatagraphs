<?php

namespace Icinga\Module\Perfdatagraphs\Common;

use Icinga\Module\Perfdatagraphs\Common\ModuleConfig;

use Icinga\Module\Icingadb\Util\PerfDataSet;

use Icinga\Application\Logger;

use Exception;

/**
 * PerfdataSource contains everything related to fetching and transforming data.
 */
trait PerfdataSource
{
    /**
     * fetchDataViaHook calls the configured PerfdataSourceHook to fetch the perfdata from the backend.
     * We use a method here, to simplify testing.
     *
     * @param string $host Name of the host
     * @param string $service Name of the service
     * @param string $checkcommand Name of the checkcommand
     * @param string $duration Duration for which to fetch the data
     *
     * @return array
     */
    public function fetchDataViaHook(string $host, string $service, string $checkcommand, string $duration): array
    {
        $data = [];

        // Get the object so that we can get its custom variables.
        $cvh = new CustomVarsHelper();
        $object = $cvh->getObjectFromString($host, $service);

        // If there's no object we can just stop here.
        if (empty($object)) {
            return $data;
        }

        $customvars = $cvh->getPerfdataGraphsConfigForObject($object);

        // List that contains the metrics we want from the backend.
        // TODO: Maybe move the filtering logic to another method for simpler testing,
        // for now it's OK since I don't know if this logic stays as is.
        $metrics = [];

        // First let's load the list of all performance data that is available for this object
        $objectPerfData = PerfDataSet::fromString($object->state->normalized_performance_data)->asArray();

        if (isset($objectPerfData)) {
            $metrics = array_map(function ($item) {
                return $item->getLabel();
            }, $objectPerfData);
        }

        // Then reduce it to only include the ones that are requested via the custom variable
        if ($customvars[$cvh::CUSTOM_VAR_CONFIG_INCLUDE] ?? false) {
            // Resolve all wildcards in the list and leave only the matching metrics.
            $metricsToInclude = $customvars[$cvh::CUSTOM_VAR_CONFIG_INCLUDE];
            $metricsIncluded = array_filter($metrics, function ($metric) use ($metricsToInclude) {
                foreach ($metricsToInclude as $pattern) {
                    if (fnmatch($pattern, $metric)) {
                        return true;
                    }
                }
                return false;
            });

            $metrics = $metricsIncluded;
        }

        // Finally remove all that are explicitly to be removed
        if ($customvars[$cvh::CUSTOM_VAR_CONFIG_EXCLUDE] ?? false) {
            $metricsToExclude = $customvars[$cvh::CUSTOM_VAR_CONFIG_EXCLUDE];
            $metricsExcluded = array_diff($metrics, $metricsToExclude);

            $metrics = $metricsExcluded;
        }

        // If the object wants the data from a custom backend
        if ($customvars[$cvh::CUSTOM_VAR_CONFIG_BACKEND] ?? false) {
            $hook = ModuleConfig::getHookByName($customvars[$cvh::CUSTOM_VAR_CONFIG_BACKEND]);
        } else {
            /** @var PerfdataSourceHook $hook */
            $hook = ModuleConfig::getHook();
        }

        // If there is no hook configured we return here.
        if (empty($hook)) {
            Logger::warning('No valid PerfdataSource hook configured.');
            return $data;
        }

        // Try to fetch the data with the hook.
        try {
            $data = $hook->fetchData($host, $service, $checkcommand, $duration, $metrics);
        } catch (Exception $e) {
            Logger::error('Failed to call PerfdataSource hook: %s', $e);
        }

        // Merge everything into the response.
        // We could have also done this browser-side but decided to do this here
        // because of simpler testability.
        $customVarsMetrics = $cvh->getPerfdataGraphsMetricsForObject($object);
        $perfdata = $cvh->mergeCustomVars($data, $customVarsMetrics);

        return $perfdata;
    }
}
