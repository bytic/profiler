<?php

namespace Nip\Profiler\Tests;

use Nip\Profiler\Profile;

/**
 * Class ProfileTest
 */
class ProfileTest extends AbstractTest
{

    public function testGetSetId()
    {
        $this->object = new Profile('56');

        self::assertEquals('56', $this->object->getId());
        self::assertFalse($this->object->hasEnded());
    }
}
