<?php

namespace Icinga\Module\Perfdatagraphs\Forms;

use Icinga\Application\Hook;
use Icinga\Forms\ConfigForm;

use DateInterval;
use Exception;

/**
 * PerfdataGraphsConfigForm represents the configuration form for the PerfdataGraphs Module.
 */
class PerfdataGraphsConfigForm extends ConfigForm
{
    public function init()
    {
        $this->setName('form_config_perfdatagraphs');
        $this->setSubmitLabel($this->translate('Save Changes'));
        $this->setValidatePartial(true);
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

    /**
     * assemble the configuration form with all available options.
     */
    public function createElements(array $formData)
    {
        // TODO: Add validator
        $this->addElement('text', 'perfdatagraphs_default_timerange', [
            'description' => t('Default time range for the "Current" button. Uses the ISO8601 duration format (e.g. PT2H, P1D). Hint: too small a value may result in invalid data'),
            'label' => 'Default Time Range (ISO8601 duration)'
        ]);

        $this->addElement('number', 'perfdatagraphs_cache_lifetime', [
            'label' => t('Cache lifetime in seconds'),
            'description' => t('How long the data for the charts will be cached by the client.'),
        ]);

        $backends = $this->listBackends();
        $choose = ['' => sprintf(' - %s - ', t('Please choose'))];

        $this->addElement(
            'select',
            'perfdatagraphs_default_backend',
            [
                'required' => true,
                'label' => $this->translate('Default Data Backend'),
                'description' => $this->translate('Default backend for the performance data graphs. With only one backend is installed, it will be used by default.'),
                'multiOptions' => array_merge($choose, array_combine($backends, $backends)),
                'class' => 'autosubmit',
            ]
        );
    }

    public function addSubmitButton()
    {
        parent::addSubmitButton()
            ->getElement('btn_submit')
            ->setDecorators(['ViewHelper']);

        $this->addElement(
            'submit',
            'backend_validation',
            [
                'ignore' => true,
                'label' => $this->translate('Validate Configuration'),
                'data-progress-label' => $this->translate('Validation In Progress'),
                'decorators' => ['ViewHelper']
            ]
        );

        $this->setAttrib('data-progress-element', 'backend-progress');
        $this->addElement(
            'note',
            'backend-progress',
            [
                'decorators' => [
                    'ViewHelper',
                    ['Spinner', ['id' => 'backend-progress']]
                ]
            ]
        );

        $this->addDisplayGroup(
            ['btn_submit', 'backend_validation', 'backend-progress'],
            'submit_validation',
            [
                'decorators' => [
                    'FormElements',
                    ['HtmlTag', ['tag' => 'div', 'class' => 'control-group form-controls']]
                ]
            ]
        );

        return $this;
    }

    public function isValidPartial(array $formData)
    {
        if ($this->getElement('backend_validation')->isChecked() && parent::isValid($formData)) {
            $validation = static::validateFormData($this);
            if ($validation !== null) {
                $this->addElement(
                    'note',
                    'inspection_output',
                    [
                        'order' => 0,
                        'value' => '<strong>' . $this->translate('Validation Log') . "</strong>\n\n"
                            . $validation['output'],
                        'decorators' => [
                            'ViewHelper',
                            ['HtmlTag', ['tag' => 'pre', 'class' => 'log-output']],
                        ]
                    ]
                );

                if (isset($validation['error'])) {
                    $this->warning(sprintf(
                        $this->translate('Failed to successfully validate the configuration: %s'),
                        $validation['error']
                    ));
                    return false;
                }
            }

            $this->info($this->translate('The configuration has been successfully validated.'));
        }

        return true;
    }

    public static function validateFormData($form): array
    {
        $di = $form->getValue('perfdatagraphs_default_timerange', 'PT12H');

        try {
            $int = new DateInterval($di);
        } catch (Exception $e) {
            return ['output' => sprintf('Failed to parse date interval "%s": %s ', $di, $e->getMessage()), 'error' => true];
        }

        return ['output' => 'OK'];
    }
}
