<?php

namespace Nip\Profiler\Adapters;

use Nip\DebugBar\DataCollector\QueryCollector;
use Nip\Profiler\Profile;

/**
 * Class DebugBar
 * @package Nip\Profiler\Adapters
 */
class DebugBar extends AbstractAdapter
{
    /**
     * @var QueryCollector
     */
    protected $collector = null;

    /**
     * @param Profile $profile
     */
    public function write(Profile $profile)
    {
        $this->getCollector()->addQuery($profile);
    }

    /**
     * @return QueryCollector
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * @param QueryCollector $colector
     */
    public function setCollector(QueryCollector $collector)
    {
        $this->collector = $collector;
    }
}
