<?php

namespace Icinga\Module\Perfdatagraphs\Clicommands;

use Icinga\Module\Perfdatagraphs\Common\PerfdataCache;
use Icinga\Cli\Command;

class CacheCommand extends Command
{
    /**
     * Clears the entire FileCache used by the Perfdatagraphs module
     *
     * USAGE
     *
     *  icingacli perfdatagraphs cache clear
     */
    public function clearAction(): void
    {
        $cache = PerfdataCache::instance('perfdatagraphs');
        $cache->clearAll();
    }
}
