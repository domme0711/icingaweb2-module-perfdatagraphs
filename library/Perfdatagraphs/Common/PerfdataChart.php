<?php

namespace Icinga\Module\Perfdatagraphs\Common;

use Icinga\Module\Perfdatagraphs\Widget\QuickActions;

use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Icingadb\Model\Service;

use ipl\Orm\Model;
use ipl\Html\HtmlElement;
use ipl\Html\Html;
use ipl\Html\ValidHtml;
use ipl\Web\Widget\Icon;
use ipl\I18n\Translation;

/**
 * PerfdataChart contains common functionality used for rendering the performance data charts.
 */
trait PerfdataChart
{
    use Translation;

    /**
     * createChart creates HTMLElements that are used to render charts in.
     */
    public function createChart(Model $object): ValidHtml
    {
        // Generic container for all elements we want to create here.
        $html = HtmlElement::create('section', ['class' => 'perfdata-charts']);

        // Check if charts are disabled for this object, if so we just return.
        $cvh = new CustomVarsHelper();
        $customvars = $cvh->getPerfdataGraphsConfigForObject($object);
        if ($customvars[$cvh::CUSTOM_VAR_CONFIG_DISABLE] ?? false) {
            return $html;
        }

        // Check if there are no perfdata for this object.
        if (empty($object->state->performance_data)) {
            return $html;
        }

        // Adjust the attributes depending on the type of object.
        if ($object instanceof Host) {
            $serviceName = $object->checkcommand_name ?? '';
            $hostName = $object->name ?? '';
            $checkCommandName = $object->checkcommand_name ?? '';
        } elseif ($object instanceof Service) {
            $serviceName = $object->name ?? '';
            $hostName = $object->host->name ?? '';
            $checkCommandName = $object->checkcommand_name ?? '';
        } else {
            // Unecessary but just to be safe.
            return $html;
        }

        // Ok so hear me out, since we are using a <canvas> to render the charts
        // we cannot use CSS classes to style the content of the chart.
        // However, we can use jQuery's .css() method to get CSS values from HTML elements,
        // which means we can create some non-visible elements with the style we want and
        // then fetch this data via JavaScript. Stupid? Maybe. Does it work? Yes.
        $colorClasses = ['axes-color', 'value-color', 'warning-color', 'critical-color'];
        foreach ($colorClasses as $class) {
            $d = HtmlElement::create('div', [
                'class' => $class,
            ]);
            $html->add($d);
        }

        // Element in which the charts will get rendered.
        // We use attributes on this elements to transport data
        // to the JavaScript part of this module.
        $chart = HtmlElement::create('div', [
            'id' => sprintf('%s-%s-%s', $hostName, $serviceName, $checkCommandName),
            'class' => 'line-chart',
            // 'data-visible-height' => 300,
            'data-host' => $hostName,
            'data-service' => $serviceName,
            'data-checkcommand' => $checkCommandName,
        ]);

        // This element can be used to show error messages when fetching data fails.
        $error = HtmlElement::create('p', [
            'class' => 'line-chart-error preformatted',
            'data-message-nodata' => $this->translate('No data received'),
            'data-message-error' => $this->translate('Error while fetching performance data'),
        ]);

        // Add a headline and all other elements to our element.
        $header = Html::tag('h2', $this->translate('Performance Data Graph'));
        $header->add(new Icon('spinner', ['class' => 'spinner']));

        $html->add($header);
        $html->add((new QuickActions()));
        $html->add($error);
        $html->add($chart);

        return $html;
    }
}
