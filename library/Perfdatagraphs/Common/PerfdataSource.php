<?php

namespace Icinga\Module\Perfdatagraphs\Common;

use Icinga\Module\Perfdatagraphs\Model\PerfdataRequest;
use Icinga\Module\Perfdatagraphs\Model\PerfdataResponse;
use Icinga\Module\Perfdatagraphs\Common\ModuleConfig;
use Icinga\Module\Perfdatagraphs\ProvidedHook\Icingadb\IcingadbSupport;

use Icinga\Module\Perfdatagraphs\Icingadb\CustomVarsHelper as IcinaDBCVH;
use Icinga\Module\Perfdatagraphs\Ido\CustomVarsHelper as IdoCVH;

use Icinga\Application\Modules\Module;
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
     * @param bool $isHostCheck Is this a Host check
     *
     * @return PerfdataResponse
     */
    public function fetchDataViaHook(string $host, string $service, string $checkcommand, string $duration, $isHostCheck): PerfdataResponse
    {
        $response = new PerfdataResponse();

        if (Module::exists('icingadb') && IcingadbSupport::useIcingaDbAsBackend()) {
            $cvh = new IcinaDBCVH();
        } else {
            $cvh = new IdoCVH();
        }

        // Get the object so that we can get its custom variables.
        $object = $cvh->getObjectFromString($host, $service);

        // If there's no object we can just stop here.
        if (empty($object)) {
            return $response;
        }

        // Load the custom variables for the metrics to include and exclude
        $customvars = $cvh->getPerfdataGraphsConfigForObject($object);

        $metricsToInclude = [];
        if ($customvars[$cvh::CUSTOM_VAR_CONFIG_INCLUDE] ?? false) {
            $metricsToInclude = $customvars[$cvh::CUSTOM_VAR_CONFIG_INCLUDE];
        }

        $metricsToExclude = [];
        if ($customvars[$cvh::CUSTOM_VAR_CONFIG_EXCLUDE] ?? false) {
            $metricsToExclude = $customvars[$cvh::CUSTOM_VAR_CONFIG_EXCLUDE];
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
            Logger::error('No valid PerfdataSource hook configured');
            $response->addError('No valid PerfdataSource hook configured');
            return $response;
        }

        // Create a new PerfdataRequest with the given parameters and custom variables
        $request = new PerfdataRequest($host, $service, $checkcommand, $duration, $isHostCheck, $metricsToInclude, $metricsToExclude);

        // Try to fetch the data with the hook.
        try {
            $response = $hook->fetchData($request);
        } catch (Exception $e) {
            $response->addError(sprintf('Failed to call PerfdataSource hook: %s', $e->getMessage()));
            return $response;
        }

        // Merge everything into the response.
        // We could have also done this browser-side but decided to do this here
        // because of simpler testability.
        $customVarsMetrics = $cvh->getPerfdataGraphsMetricsForObject($object);

        $response->mergeCustomVars($customVarsMetrics);

        return $response;
    }
}
