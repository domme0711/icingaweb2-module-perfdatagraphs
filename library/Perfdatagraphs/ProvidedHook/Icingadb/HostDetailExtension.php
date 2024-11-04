<?php

namespace Icinga\Module\Perfdatagraphs\ProvidedHook\Icingadb;

use Icinga\Module\Perfdatagraphs\Common\PerfdataChart;

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
        // Get the configured element for the host.
        $chart = $this->createChart($host);

        if (empty($chart)) {
            // Probably unecessary but just to be safe.
            return HtmlString::create('');
        }

        return HtmlString::create($chart);
    }
}
