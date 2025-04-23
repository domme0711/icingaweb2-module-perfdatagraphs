<?php

namespace Icinga\Module\Perfdatagraphs\Ido;

use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Module\Monitoring\Object\Host;
use Icinga\Module\Monitoring\Object\MonitoredObject;
use Icinga\Module\Monitoring\Object\Service;

use Icinga\Exception\NotFoundError;

/**
 * CustomVarsHelper is a helper class to work with custom variables of Icinga objects.
 */
class CustomVarsHelper
{
    // Name of all the custom variables we use.
    public const CUSTOM_VAR_CONFIG_PREFIX  = 'perfdatagraphs_config';
    public const CUSTOM_VAR_CONFIG_INCLUDE = 'perfdatagraphs_config_metrics_include';
    public const CUSTOM_VAR_CONFIG_EXCLUDE = 'perfdatagraphs_config_metrics_exclude';
    public const CUSTOM_VAR_CONFIG_HIGHLIGHT = 'perfdatagraphs_config_highlight';
    public const CUSTOM_VAR_CONFIG_DISABLE = 'perfdatagraphs_config_disable';
    public const CUSTOM_VAR_CONFIG_BACKEND = 'perfdatagraphs_config_backend';
    public const CUSTOM_VAR_METRICS = 'perfdatagraphs_metrics';

    /**
     * getObjectFromString returns a Host or Service object from the database given the strings.
     *
     * @param string $host host name for the object
     * @param string $service service name for the object
     * @param bool $isHostCheck Is this a Host check
     * @return ?MonitoredObject
     */
    public function getObjectFromString(string $host, string $service, bool $isHostCheck): ?MonitoredObject
    {
        // Determine the type if Model we need to use to get the data.
        try {
            if ($isHostCheck) {
                $object = new Host(MonitoringBackend::instance(), $host);
            } else {
                $object = new Service(MonitoringBackend::instance(), $host, $service);
            }
        } catch (NotFoundError $e) {
            // Maybe there's a better way but OK for now.
            return null;
        }

        $object->fetch();

        return $object;
    }

    /**
     * getPerfdataGraphsConfigForObject returns the this module's config custom variables for an object.
     *
     * @param MonitoredObject $object Icinga Object
     * @return array
     */
    public function getPerfdataGraphsConfigForObject(MonitoredObject $object): array
    {
        $data = [];

        if (empty($object)) {
            return $data;
        }

        // Get the object's custom variables and decode them
        $result = [];

        if (empty($object->customvars)) {
            return $result;
        }

        foreach ($object->customvars as $key => $value) {
            // We are only interested in our custom vars
            if (str_starts_with($key, self::CUSTOM_VAR_CONFIG_PREFIX)) {
                $result[$value] = json_decode($value, true) ?? $value;
            }
        }

        return $result;
    }

    /**
     * getPerfdataGraphsConfigForObject returns the this module's metrics custom variables for an object.
     *
     * @param MonitoredObject $object Icinga Object
     * @return array
     */
    public function getPerfdataGraphsMetricsForObject(MonitoredObject $object): array
    {
        $data = [];

        if (empty($object)) {
            return $data;
        }

        $result = [];

        if (empty($object->customvars)) {
            return $result;
        }

        // Get the object's custom variables and decode them
        foreach ($object->customvars as $key => $value) {
            // We are only interested in our custom vars
            if (str_starts_with($key, self::CUSTOM_VAR_METRICS)) {
                $result[$value] = json_decode($value, true) ?? $value;
            }
        }

        return $result[self::CUSTOM_VAR_METRICS] ?? [];
    }
}
