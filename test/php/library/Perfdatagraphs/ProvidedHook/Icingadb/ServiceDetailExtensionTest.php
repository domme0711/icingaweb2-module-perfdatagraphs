<?php

namespace Tests\Icinga\Module\Perfdatagraphs;

use Icinga\Module\Perfdatagraphs\ProvidedHook\Icingadb\ServiceDetailExtension;

use Icinga\Module\Icingadb\Model\Service;
use Icinga\Module\Icingadb\Model\Host;

use PHPUnit\Framework\TestCase;

final class ServiceDetailExtensionTest extends TestCase
{
    public function test_ServiceDetailExtension(): void
    {
        $s = new ServiceDetailExtension();

        $service = new Service();
        $service->name = 'load';
        $service->checkcommand_name = 'load';
        $host = new Host();
        $host->name = 'host2';

        $service->host = $host;

        $g = $s->getHtmlForObject($service);
        $html = $g->render();

        // Asset dataset is present.
        $this->assertStringContainsString('data-host="host2"', $html);
        $this->assertStringContainsString('data-service="load"', $html);
        $this->assertStringContainsString('data-checkcommand="load"', $html);
        // Asset quick actions are present.
        $this->assertStringContainsString('data-duration="PT12H"', $html);
        $this->assertStringContainsString('data-duration="P7D"', $html);
        $this->assertStringContainsString('data-duration="P1Y"', $html);
    }

    public function test_ServiceDetailExtension_with_no_service(): void
    {
        $s = new ServiceDetailExtension();

        $service = new Service();

        $g = $s->getHtmlForObject($service);
        $html = $g->render();

        $this->assertStringContainsString('data-host=""', $html);
        $this->assertStringContainsString('data-service=""', $html);
        $this->assertStringContainsString('data-checkcommand=""', $html);
    }
}
