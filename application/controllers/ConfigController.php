<?php

namespace Icinga\Module\Perfdatagraphs\Controllers;

use Icinga\Module\Perfdatagraphs\Forms\PerfdataGraphsConfigForm;

use Icinga\Application\Config;
use Icinga\Web\Widget\Tabs;

use ipl\Html\HtmlString;
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
        $config = Config::module('perfdatagraphs');

        $form = new PerfdataGraphsConfigForm();
        $form->setIniConfig($config);
        $form->handleRequest();

        $this->mergeTabs($this->Module()->getConfigTabs()->activate('general'));

        $this->addContent(new HtmlString($form->render()));
    }

    /**
     * Merge tabs with other tabs contained in this tab panel.
     *
     * @param Tabs $tabs
     */
    protected function mergeTabs(Tabs $tabs): self
    {
        foreach ($tabs->getTabs() as $tab) {
            $this->tabs->add($tab->getName(), $tab);
        }

        return $this;
    }
}
