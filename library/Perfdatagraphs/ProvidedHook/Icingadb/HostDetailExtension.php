<?php

namespace Icinga\Module\Perfdatagraphs\ProvidedHook\Icingadb;

use Icinga\Module\Perfdatagraphs\Common\PerfdataChart;
use Icinga\Module\Perfdatagraphs\Icingadb\CustomVarsHelper;

use Icinga\Module\Icingadb\Hook\HostDetailExtensionHook;
use Icinga\Module\Icingadb\Model\Host;

use ipl\Html\HtmlString;
use ipl\Html\ValidHtml;

/**
 * HostDetailExtension adds the Chart HTML for Host objects.
 */
class HostDetailExtension extends HostDetailExtensionHook
{
    use PerfdataChart;

    /**
     * getHtmlForObject returns the Chart HTML.
     */
    public function getHtmlForObject(Host $host): ValidHtml
    {
        $serviceName = $host->checkcommand_name ?? '';
        $hostName = $host->name ?? '';
        $checkCommandName = $host->checkcommand_name ?? '';

        $cvh = new CustomVarsHelper();
        $customvars = $cvh->getPerfdataGraphsConfigForObject($host);

        // Check if charts are disabled for this object, if so we just return.
        if ($customvars[$cvh::CUSTOM_VAR_CONFIG_DISABLE] ?? false) {
            return HtmlString::create('');
        }

        // Get the configured element for the host.
        $chart = $this->createChart($hostName, $serviceName, $checkCommandName);

        if (empty($chart)) {
            // Probably unecessary but just to be safe.
            return HtmlString::create('');
        }

        return HtmlString::create($chart);
    }
}
