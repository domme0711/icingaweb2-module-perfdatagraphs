<?php

namespace Icinga\Module\Perfdatagraphs\Forms;

use Icinga\Application\Hook;
use Icinga\Forms\ConfigForm;

/**
 * PerfdataGraphsConfigForm represents the configuration form for the PerfdataGraphs Module.
 */
class PerfdataGraphsConfigForm extends ConfigForm
{
    public function init()
    {
        $this->setName('form_config_perfdatagraphs');
        $this->setSubmitLabel($this->translate('Save Changes'));
    }

    /**
     * assemble the configuration form with all available options.
     */
    public function createElements(array $formData)
    {
        $backends = $this->listBackends();
        $choose = ['' => sprintf(' - %s - ', t('Please choose'))];

        $this->addElement(
            'select',
            'perfdatagraphs_default_backend',
            [
                'required' => true,
                'label' => $this->translate('Default Data Backend'),
                'description' => $this->translate('Default backend for the performance data graphs'),
                'multiOptions' => array_merge($choose, array_combine($backends, $backends)),
                'class' => 'autosubmit',
            ]
        );
    }

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
}
