<?php

namespace Icinga\Module\Perfdatagraphs\Common;

use Icinga\Module\Perfdatagraphs\Widget\QuickActions;

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
     *
     * @param string $host Name of the host
     * @param string $service Name of the service
     * @param string $checkcommand Name of the checkcommand
     * @param bool $isHostCheck Is this a Host check
     *
     * @return ValidHtml
     */
    public function createChart(string $hostName, string $serviceName, string $checkCommandName, bool $isHostCheck): ValidHtml
    {
        // Generic container for all elements we want to create here.
        $main = HtmlElement::create('div', ['class' => 'perfdata-charts']);

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
            $main->add($d);
        }

        // Where we store all elements for the charts.
        $charts = HtmlElement::create('div', [
            'class' => 'perfdata-charts-container collapsible',
            // Note: We could have a configuration option to change the
            // "always collapsed" behaviour
            'data-visible-height' => 0,
        ]);

        // Element in which the charts will get rendered.
        // We use attributes on this elements to transport data
        // to the JavaScript part of this module.
        $chart = HtmlElement::create('div', [
            'id' => sprintf('%s-%s-%s', $hostName, $serviceName, $checkCommandName),
            'class' => 'line-chart',
            'data-host' => $hostName,
            'data-ishostcheck' => $isHostCheck ? 'true': 'false',
            'data-service' => $serviceName,
            'data-checkcommand' => $checkCommandName,
        ]);

        // This element can be used to show error messages when fetching data fails.
        $error = HtmlElement::create('p', [
            'class' => 'line-chart-error preformatted',
            'data-message-nodata' => $this->translate('No data received'),
            'data-message-error' => $this->translate('Error while fetching performance data'),
        ]);

        $config = ModuleConfig::getConfig();

        // Add a headline and all other elements to our element.
        $header = Html::tag('h2', $this->translate('Performance Data Graph'));
        $header->add(new Icon('spinner', ['class' => 'spinner']));

        $main->add($header);
        $main->add($error);

        $charts->add((new QuickActions($config['default_timerange'])));
        $charts->add($chart);

        $main->add($charts);

        return $main;
    }
}
