<?php

namespace Tests\Icinga\Module\Perfdatagraphs;

use Icinga\Module\Perfdatagraphs\Widget\QuickActions;

use PHPUnit\Framework\TestCase;

final class QuickActionsTest extends TestCase
{
    public function test_assemble()
    {

        $qa = new QuickActions('FOOBAR');

        $this->assertStringContainsString('data-duration="FOOBAR"', $qa->render());
    }
}
