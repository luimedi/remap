<?php

namespace Tests\DataObjectTest;

use Luimedi\Remap\Data;
use PHPUnit\Framework\TestCase;

use Tests\DataObjectTest\Inner;
use Tests\DataObjectTest\User;
use Tests\DataObjectTest\Root;

class DataObjectTest extends TestCase
{
    public function testGetObjectNestedProperty()
    {
        $inner = new Inner();
        $user = new User($inner);
        $root = new Root($user);

        $city = Data::get($root, 'user.address.city');

        $this->assertSame('Metropolis', $city);
    }
}
