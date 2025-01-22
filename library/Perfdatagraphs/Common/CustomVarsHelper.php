<?php

namespace Icinga\Module\Perfdatagraphs\Common;

use Icinga\Exception\NotFoundError;
use Icinga\Module\Icingadb\Common\Auth;
use Icinga\Module\Icingadb\Common\Database;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Icingadb\Model\Service;

use ipl\Stdlib\Filter;

use ipl\Orm\Model;

/**
 * CustomVarsHelper is a helper class to work with custom variables of Icinga2 objects.
 */
class CustomVarsHelper
{
    use Database;
    use Auth;

    // Name of all the custom variables we use.
    public const CUSTOM_VAR_CONFIG_PREFIX  = 'perfdatagraphs_config';
    public const CUSTOM_VAR_CONFIG_INCLUDE = 'perfdatagraphs_config_metrics_include';
    public const CUSTOM_VAR_CONFIG_EXCLUDE = 'perfdatagraphs_config_metrics_exclude';
    public const CUSTOM_VAR_CONFIG_DISABLE = 'perfdatagraphs_config_disable';
    public const CUSTOM_VAR_METRICS = 'perfdatagraphs_metrics';

    /**
     * Returns the Host object from the database given the hostname.
     *
     * @param string $host host name for the object
     * @throws NotFoundError
     * @return Host
     */
    protected function getHostObject(string $host): Host
    {
        $query = Host::on($this->getDb())->with(['state']);

        $query->filter(Filter::equal('name', $host));

        $this->applyRestrictions($query);

        $host = $query->first();

        if ($host === null) {
            throw new NotFoundError(t('Host not found'));
        }

        return $host;
    }

    /**
     * Returns the Service object from the database given the hostname/servicename
     *
     * @param string $host host name for the object
     * @param string $service service name for the object
     * @throws NotFoundError
     * @return Service
     */
    protected function getServiceObject(string $host, string $service): Service
    {
        $query = Service::on($this->getDb())->with(['state', 'host']);

        $query->filter(Filter::equal('name', $service));
        $query->filter(Filter::equal('host.name', $host));

        $this->applyRestrictions($query);

        $service = $query->first();

        if ($service === null) {
            throw new NotFoundError(t('Service not found'));
        }

        return $service;
    }

    /**
     * getObjectFromString returns a Host or Service object from the database given the strings.
     *
     * @param string $host host name for the object
     * @param string $service service name for the object
     * @return Model
     */
    public function getObjectFromString(string $host, string $service): ?Model
    {
        if ($service === 'hostalive') {
            $service = null;
        }

        // Determine the type if Model we need to use to get the data.
        try {
            if (empty($service)) {
                $object = $this->getHostObject($host);
            } else {
                $object = $this->getServiceObject($host, $service);
            }
        } catch (NotFoundError $e) {
            // Maybe there's a better way but OK for now.
            return null;
        }

        return $object;
    }

    /**
     * getPerfdataGraphsConfigForObject returns the this module's config custom variables for an object.
     *
     * @param Model $object Icinga Object
     * @return array
     */
    public function getPerfdataGraphsConfigForObject(Model $object): array
    {
        $data = [];

        if (empty($object)) {
            return $data;
        }

        // Get the object's custom variables and decode them
        $customvars = $object->customvar->columns(['name', 'value']);

        $result = [];
        foreach ($customvars as $row) {
            // We are only interested in our custom vars
            if (str_starts_with($row->name, self::CUSTOM_VAR_CONFIG_PREFIX)) {
                $result[$row->name] = json_decode($row->value, true) ?? $row->value;
            }
        }

        return $result;
    }

    /**
     * getPerfdataGraphsConfigForObject returns the this module's metrics custom variables for an object.
     *
     * @param Model $object Icinga Object
     * @return array
     */
    public function getPerfdataGraphsMetricsForObject(Model $object): array
    {
        $data = [];

        if (empty($object)) {
            return $data;
        }

        // Get the object's custom variables and decode them
        $customvars = $object->customvar->columns(['name', 'value']);

        $result = [];
        foreach ($customvars as $row) {
            // We are only interested in our custom vars
            if ($row->name === self::CUSTOM_VAR_METRICS) {
                $result[$row->name] = json_decode($row->value, true) ?? $row->value;
            }
        }

        return $result[self::CUSTOM_VAR_METRICS] ?? [];
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
