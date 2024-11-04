<?php

namespace Icinga\Module\Perfdatagraphs\Widget;

use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\I18n\Translation;
use ipl\Web\Widget\Icon;

/**
 * The QuickActions adds links for selecting the duration.
 * We use JavaScript to read the data attributes
 * when the links are clicked.
 */
class QuickActions extends BaseHtmlElement
{
    use Translation;

    protected $tag = 'ul';

    protected $defaultAttributes = ['class' => 'quick-actions'];

    /**
     * Implement the BaseHtmlElement assemble method.
     * Hint: We do not use a loop to facilitate translation.
     */
    protected function assemble(): void
    {
        $current = Html::tag(
            'a',
            [
                'href' => '#',
                'data-duration' => 'PT12H',
                'class' => 'action-link',
                'title' => $this->translate('Show performance data for the last 12 hours'),
            ],
            [ new Icon('calendar'), $this->translate('Current') ]
        );

        $this->add(Html::tag('li', $current));

        $day = Html::tag(
            'a',
            [
                'href' => '#',
                'data-duration' => 'P1D',
                'class' => 'action-link',
                'title' => $this->translate('Show performance data for the last day'),
            ],
            [ new Icon('calendar'), $this->translate('Day') ]
        );

        $week = Html::tag(
            'a',
            [
                'href' => '#',
                'data-duration' => 'P7D',
                'class' => 'action-link',
                'title' => $this->translate('Show performance data for the last week'),
            ],
            [ new Icon('calendar'), $this->translate('Week') ]
        );

        $month = Html::tag(
            'a',
            [
                'href' => '#',
                'data-duration' => 'P30D',
                'class' => 'action-link',
                'title' => $this->translate('Show performance data for the last month'),
            ],
            [ new Icon('calendar'), $this->translate('Month') ]
        );

        $year = Html::tag(
            'a',
            [
                'href' => '#',
                'data-duration' => 'P1Y',
                'class' => 'action-link',
                'title' => $this->translate('Show performance data for the last year'),
            ],
            [ new Icon('calendar'), $this->translate('Year') ]
        );

        $this->add(Html::tag('li', $day));
        $this->add(Html::tag('li', $week));
        $this->add(Html::tag('li', $month));
        $this->add(Html::tag('li', $year));
    }
}
