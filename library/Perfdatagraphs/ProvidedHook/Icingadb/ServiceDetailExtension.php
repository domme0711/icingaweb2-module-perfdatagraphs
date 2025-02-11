<?php

namespace Icinga\Module\Perfdatagraphs\ProvidedHook\Icingadb;

use Icinga\Module\Perfdatagraphs\Common\PerfdataChart;
use Icinga\Module\Perfdatagraphs\Icingadb\CustomVarsHelper;

use Icinga\Module\Icingadb\Hook\ServiceDetailExtensionHook;
use Icinga\Module\Icingadb\Model\Service;

use ipl\Html\HtmlString;
use ipl\Html\ValidHtml;

/**
 * ServiceDetailExtension adds the Chart HTML for Service objects.
 */
class ServiceDetailExtension extends ServiceDetailExtensionHook
{
    use PerfdataChart;

    /**
     * getHtmlForObject returns the Chart HTML.
     */
    public function getHtmlForObject(Service $service): ValidHtml
    {
        $serviceName = $service->name ?? '';
        $hostName = $service->host->name ?? '';
        $checkCommandName = $service->checkcommand_name ?? '';

        $cvh = new CustomVarsHelper();
        $customvars = $cvh->getPerfdataGraphsConfigForObject($service);

        // Check if charts are disabled for this object, if so we just return.
        if ($customvars[$cvh::CUSTOM_VAR_CONFIG_DISABLE] ?? false) {
            return HtmlString::create('');
        }

        // Get the configured element for the service.
        $chart = $this->createChart($hostName, $serviceName, $checkCommandName);

        if (empty($chart)) {
            // Probably unecessary but just to be safe.
            return HtmlString::create('');
        }

        return HtmlString::create($chart);
    }
}
