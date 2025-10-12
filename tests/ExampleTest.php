<?php

namespace Tests;

use Luimedi\Remap\Mapper;
use PHPUnit\Framework\TestCase;
use Tests\Demo\Input;
use Tests\Demo\Output;

class ExampleTest extends TestCase
{
    public function testExample()
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, Output::class);

        $result = $mapper->map(new Input(name: 'Luis', age: 35));
        $this->assertInstanceOf(Output::class, $result);

        $this->assertSame('Luis', $result->name);
        $this->assertSame(35, $result->age);
    }
}