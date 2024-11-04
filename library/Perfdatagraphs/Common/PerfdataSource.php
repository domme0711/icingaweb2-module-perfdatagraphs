<?php

namespace Icinga\Module\Perfdatagraphs\Common;

use Icinga\Module\Perfdatagraphs\Common\ModuleConfig;

use Icinga\Application\Logger;
use Icinga\Module\Icingadb\Common\Database;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Icingadb\Model\Service;

use ipl\Stdlib\Filter;

use Exception;

/**
 * PerfdataSource contains everything related to fetching and transforming data.
 */
trait PerfdataSource
{
    use Database;

    /**
     * fetchDataViaHook calls the configured PerfdataSourceHook to fetch the perfdata from the backend.
     * We use a method here, to simplify testing.
     *
     * @param string $host Name of the host
     * @param string $service Name of the service
     * @param string $checkcommand Name of the checkcommand
     * @param string $duration Duration for which to fetch the data
     * @param array $metrics List of metrics to fetch
     *
     * @return array
     */
    public function fetchDataViaHook(string $host, string $service, string $checkcommand, string $duration, array $metrics): array
    {
        $data = [];

        /** @var PerfdataSourceHook $hook */
        $hook = ModuleConfig::getHook();

        // If there is no hook configured we return here.
        if (empty($hook)) {
            Logger::warning('No PerfdataSource hook configured.');
            return $data;
        }

        // Try to fetch the data with the hook.
        try {
            $data = $hook->fetchData($host, $service, $checkcommand, $duration, $metrics);
        } catch (Exception $e) {
            Logger::error('Failed to call PerfdataSource hook: %s', $e);
        }

        return $data;
    }

    /**
     * getCustomVarsFromDatabase returns an Icinga2 object given a string.
     * We use a method here, to simplify testing.
     *
     * @param string $hostName Name of the host
     * @param string $serviceName Name of the service
     *
     * @return array
     */
    public function getCustomVarsFromDatabase(string $hostName, string $serviceName): array
    {
        // Use the IcingaDB Database Connection
        $db = $this->getDb();

        // Determine the type if Model we need to use to get the data
        if (empty($serviceName)) {
            $query = Host::on($this->getDb());
            $query->filter(Filter::equal('host.name', $hostName));
        } else {
            $query = Service::on($this->getDb());
            $query->filter(Filter::all(
                Filter::equal('service.name', $serviceName),
                Filter::equal('host.name', $hostName)
            ));
        }

        // Resolve the query. We could maybe return here, and do the following
        // in another method.
        $object = $query->first();

        $data = [];

        if (empty($object)) {
            return $data;
        }

        // Get the object's custom variables and decode them
        $customvars = $object->customvar->columns(['name', 'value']);

        $customvar_key = 'graphs';

        $result = [];
        foreach ($customvars as $row) {
            // We are only interested in our custom vars
            if ($row->name === $customvar_key) {
                $result[$row->name] = json_decode($row->value, true) ?? $row->value;
            }
        }

        return $result[$customvar_key] ?? [];
    }

    /**
     * mergeCustomVars merges the performance data with the custom vars,
     * so that each series receives its corresponding vars.
     * CustomVars override data in the PerfData.
     *
     * We could have also done this browser-side but decided to do this here
     * because of simpler testability. We could change that if browser-side merging
     * is more performant.
     *
     * If the functionality remains here, we should optimize if for performance.
     *
     * @param array $perfdata The entire performance dataset
     * @param array $customvars The custom variables for the given object
     * @return array
     */
    public function mergeCustomVars(array $perfdata, array $customvars): array
    {
        // If we don't have any custom vars return early
        if (empty($customvars)) {
            return $perfdata;
        }

        foreach ($perfdata as $dkey => $dataset) {
            $title = $dataset['title'] ?? 'No Name';
            // Merge the custom vars for the entire dataset
            if (array_key_exists($title, $customvars)) {
                if (isset($customvars[$title]['unit'])) {
                    $perfdata[$dkey]['unit'] = $customvars[$title]['unit'];
                }
                if (isset($customvars[$title]['fill'])) {
                    $perfdata[$dkey]['fill'] = $customvars[$title]['fill'];
                }
                if (isset($customvars[$title]['stroke'])) {
                    $perfdata[$dkey]['stroke'] = $customvars[$title]['stroke'];
                }
            }
        }

        return $perfdata;
    }
}
