<?php

namespace Tests\Icinga\Module\Perfdatagraphs;

use Icinga\Module\Perfdatagraphs\Model\PerfdataRequest;

use PHPUnit\Framework\TestCase;

final class PerfdataRequestTest extends TestCase
{
    public function test_perfdatarequest()
    {
        $pfr = new PerfdataRequest("host", "service", "check", "PT12H", true);

        $this->assertEquals("host", $pfr->getHostname());
        $this->assertEquals("service", $pfr->getServicename());
        $this->assertEquals("check", $pfr->getCheckcommand());
        $this->assertEquals("PT12H", $pfr->getDuration());
        $this->assertTrue($pfr->isHostCheck());
        $this->assertEquals([], $pfr->getIncludeMetrics());
        $this->assertEquals([], $pfr->getExcludeMetrics());

        $pfr = new PerfdataRequest("host", "service", "check", "PT12H", false, ["foobar"], ["barfoo"]);

        $this->assertEquals("host", $pfr->getHostname());
        $this->assertEquals("service", $pfr->getServicename());
        $this->assertEquals("check", $pfr->getCheckcommand());
        $this->assertEquals("PT12H", $pfr->getDuration());
        $this->assertFalse($pfr->isHostCheck());
        $this->assertEquals(["foobar"], $pfr->getIncludeMetrics());
        $this->assertEquals(["barfoo"], $pfr->getExcludeMetrics());
    }
}
