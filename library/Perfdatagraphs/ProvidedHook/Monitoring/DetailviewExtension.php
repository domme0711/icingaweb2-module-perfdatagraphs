<?php

namespace Icinga\Module\Perfdatagraphs\ProvidedHook\Monitoring;

use Icinga\Module\Perfdatagraphs\Ido\PerfdataChart;

use Icinga\Module\Monitoring\Object\MonitoredObject;
use Icinga\Module\Monitoring\Hook\DetailviewExtensionHook;

use ipl\Html\HtmlString;

class DetailviewExtension extends DetailviewExtensionHook
{
    use PerfdataChart;

    public function getHtmlForObject(MonitoredObject $object)
    {
        // Get the configured element for the host.
        $chart = $this->createChart($object);

        if (empty($chart)) {
            // Probably unecessary but just to be safe.
            return HtmlString::create('');
        }

        return HtmlString::create($chart);
    }
}
