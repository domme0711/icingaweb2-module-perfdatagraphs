<?php

namespace Icinga\Module\Perfdatagraphs\Forms;

use Icinga\Application\Hook;

use ipl\Web\Compat\CompatForm;

/**
 * PerfdataGraphsConfigForm represents the configuration form for the PerfdataGraphs Module.
 */
class PerfdataGraphsConfigForm extends CompatForm
{
    /**
     * listBackends returns a list of all available PerfdataSource hooks.
     */
    protected function listBackends(): array
    {
        $hooks = Hook::all('perfdatagraphs/PerfdataSource');

        $enum = array();
        foreach ($hooks as $hook) {
            $enum[get_class($hook)] = $hook->getName();
        }
        asort($enum);

        return $enum;
    }

    /**
     * assemble the configuration form with all available options.
     */
    protected function assemble(): void
    {
        $backends = $this->listBackends();

        $this->addElement('select', 'backend', [
            'description' => t('Data backend for the Performance Data Graphs'),
            'label' => t('Performance Data Backend'),
            'multiOptions' => array_merge(
                ['' => sprintf(' - %s - ', t('Please choose'))],
                array_combine($backends, $backends)
            ),
            'disable' => [''],
            'required' => true,
            'value' => ''
        ]);

        $this->addElement(
            'submit',
            'submit',
            [
                'label' => $this->translate('Save Changes')
            ]
        );
    }
}
