<?php

namespace Tests\EngineTest;

use Luimedi\Remap\Exception\BindingNotFoundException;
use Luimedi\Remap\Exception\RemapException;
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
        $this->expectException(BindingNotFoundException::class);

        $mapper = new Mapper();

        $mapper->map(new class {});
    }

    public function testLibraryExceptionsCanBeCaughtByBaseRemapException()
    {
        $mapper = new Mapper();

        try {
            $mapper->map(new class {});
            $this->fail('Expected an exception to be thrown');
        } catch (\Throwable $exception) {
            $this->assertInstanceOf(RemapException::class, $exception);
        }
    }
}
