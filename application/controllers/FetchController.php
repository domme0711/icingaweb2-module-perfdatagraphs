<?php

namespace Icinga\Module\Perfdatagraphs\Controllers;

use Icinga\Module\Perfdatagraphs\Common\PerfdataSource;

use Icinga\Util\Json;

use ipl\Web\Compat\CompatController;

/**
 * FetchController calls the Hook to fetch the data for the charts.
 */
class FetchController extends CompatController
{
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

        // Fetch the perfdata for a given object via the hook.
        $perfdata = $this->fetchDataViaHook($host, $service, $checkcommand, $duration);

        if (! $perfdata->isValid()) {
            // If the data is not valid, return an error
            $perfdata->addError('Invalid data received');
        }

        // Use gzip encoding to reduce the amount of transfered data
        $body = gzencode(Json::sanitize($perfdata));

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
