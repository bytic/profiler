<?php

namespace Nip\Profiler\Adapters;

use Nip\Profiler\Profile;

/**
 * Class AbstractAdapter
 * @package Nip\Profiler\Adapters
 */
abstract class AbstractAdapter
{
    abstract public function write(Profile $profile);
}
