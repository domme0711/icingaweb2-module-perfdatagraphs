<?php

namespace Icinga\Module\Perfdatagraphs\ProvidedHook\Icingadb;

use Icinga\Module\Perfdatagraphs\Icingadb\PerfdataChart;

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
        // Get the configured element for the service.
        $chart = $this->createChart($service);

        if (empty($chart)) {
            // Probably unecessary but just to be safe.
            return HtmlString::create('');
        }

        return HtmlString::create($chart);
    }
}
