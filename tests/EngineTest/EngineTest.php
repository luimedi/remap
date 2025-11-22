<?php

namespace Tests\EngineTest;

use InvalidArgumentException;
use Luimedi\Remap\Mapper;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
    public function testCallableResolverIsUsed()
    {
        $mapper = new Mapper();

        $mapper->bind(Input::class, function ($obj, $ctx) {
            return Output::class;
        });

        $result = $mapper->map(new Input());

        $this->assertInstanceOf(Output::class, $result);
    }

    public function testResolveThrowsWhenNoBinding()
    {
        $this->expectException(InvalidArgumentException::class);

        $mapper = new Mapper();

        $mapper->map(new class {});
    }
}
