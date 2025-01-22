<?php

namespace Icinga\Module\Perfdatagraphs\Controllers;

use Icinga\Module\Perfdatagraphs\Forms\PerfdataGraphsConfigForm;

use Icinga\Application\Config;
use Icinga\Web\Notification;
use Icinga\Web\Widget\Tabs;

use ipl\Web\Compat\CompatController;

/**
 * ConfigController manages the configuration for the Graphs Module.
 */
class ConfigController extends CompatController
{
    protected bool $disableDefaultAutoRefresh = true;

    /**
     * Initialize the Controller.
     */
    public function init(): void
    {
        // Assert the user has access to this controller.
        $this->assertPermission('config/modules');
        parent::init();
    }

    /**
     * generalAction provides the configuration form.
     * For now we have everything on a single Tab, might be extended in the future.
     */
    public function generalAction(): void
    {
        // Get the configuration for this module.
        $config = Config::module('perfdatagraphs');

        // Render the ConfigForm and handle requests.
        $form = (new PerfdataGraphsConfigForm())
            ->populate($config->getSection('general'))
            ->on(PerfdataGraphsConfigForm::ON_SUCCESS, function ($form) use ($config) {
                $config->setSection('general', $form->getValues());
                $config->saveIni();
                Notification::success($this->translate('New configuration has successfully been stored'));
            })->handleRequest($this->getServerRequest());

        $this->mergeTabs($this->Module()->getConfigTabs()->activate('general'));

        $this->addContent($form);
    }

    /**
     * Merge tabs with other tabs contained in this tab panel.
     *
     * @param Tabs $tabs
     */
    protected function mergeTabs(Tabs $tabs): void
    {
        foreach ($tabs->getTabs() as $tab) {
            $this->tabs->add($tab->getName(), $tab);
        }
    }
}
