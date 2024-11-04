<?php

namespace Icinga\Module\Perfdatagraphs\Common;

use Icinga\Module\Perfdatagraphs\Hook\PerfdataSourceHook;

use Icinga\Application\Config;
use Icinga\Application\Logger;
use Icinga\Application\Hook;

use Exception;

/**
 * ModuelConfig is a helper class to safely access this module's configuration.
 */
class ModuleConfig
{
    /**
     * getHook loads the configured hook from the configuration
     *
     * @return ?PerfdataSourceHook
     */
    public static function getHook(Config $moduleConfig = null): ?PerfdataSourceHook
    {
        // We just default to first hook we find.
        $default = Hook::first('perfdatagraphs/PerfdataSource');

        // Try to load the configuration
        if ($moduleConfig === null) {
            try {
                $moduleConfig = Config::module('graphs');
            } catch (Exception $e) {
                Logger::error('Failed to load Performance Data Graphs module configuration: %s', $e);
                return $default;
            }
        }

        $configuredHookName = $moduleConfig->get('general', 'backend', 'No such hook');

        $hooks = Hook::all('perfdatagraphs/PerfdataSource');
        // See if we can find the configured hook in the available hooks
        // If not then we return the first we find, which could still be none
        foreach ($hooks as $hook) {
            if ($configuredHookName === $hook->getName()) {
                return $hook;
            }
        }

        return $default;
    }
}
