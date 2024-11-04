<?php

namespace Icinga\Module\Perfdatagraphs\Controllers;

use Icinga\Module\Perfdatagraphs\Common\PerfdataSource;

use Icinga\Module\Icingadb\Common\Database;
use Icinga\Util\Json;

use ipl\Web\Compat\CompatController;

/**
 * FetchController calls the Hook to fetch the data for the charts.
 */
class FetchController extends CompatController
{
    /**
     * @var Database
     */
    use Database;

    /**
     * @var PerfdataSource
     */
    use PerfdataSource;

    /**
     * indexAction is called from the module.js to fetch the data
     * to be rendered.
     */
    public function indexAction()
    {
        // Retrieve the URL parameters.
        $host = $this->params->getRequired('host');
        $service = $this->params->getRequired('service');
        $checkcommand = $this->params->getRequired('checkcommand');
        $duration = $this->params->get('duration', 'PT12H');
        $metrics = [];

        // Get the custom vars for the given object.
        $customvars = $this->getCustomVarsFromDatabase($host, $service);
        $metrics = $customvars['metrics'] ?? [];

        // Fetch the perfdata for a given object via the hook.
        $perfdata = $this->fetchDataViaHook($host, $service, $checkcommand, $duration, $metrics);

        // Merge everything into the response.
        // We could have also done this browser-side but decided to do this here
        // because of simpler testability.
        $data = $this->mergeCustomVars($perfdata, $customvars);
        // Use gzip encoding to reduce the amount of transfered data
        $body = gzencode(Json::sanitize($data));

        // Return the everything as a JSON reposonse.
        $response = $this->getResponse();
        $response
            ->setHeader('Content-Type', 'application/json')
            // We could maybe do a more dynamic max-age, based on the duration for example
            ->setHeader('Cache-Control', sprintf('public, max-age=%s', 360), true)
            ->setHeader('Content-Encoding', 'gzip')
            ->setHeader('Content-Length', strlen($body))
            ->appendBody($body)
            ->sendResponse();

        exit;
    }
}
