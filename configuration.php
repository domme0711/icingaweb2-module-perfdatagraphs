<?php

use Icinga\Application\Modules\Module;

/** @var \Icinga\Application\Modules\Module $this */

$this->provideConfigTab(
    'general',
    [
        'title' => $this->translate('General'),
        'label' => $this->translate('General'),
        'url' => 'config/general'
    ]
);

$this->provideCssFile('vendor/uPlot.css');
$this->provideJsFile('vendor/uPlot.iife.min.js');

// We only need this for the Monitoring Module
if (!Module::exists('icingadb')) {
    $this->provideCssFile('quick-actions.less');
}
