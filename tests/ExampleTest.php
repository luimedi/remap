<?php

namespace Tests;

use DateTimeImmutable;
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

        $result = $mapper->map(new Input(name: 'Luis', birthdate: new DateTimeImmutable('1988-01-01')));
        $this->assertInstanceOf(Output::class, $result);

        $this->assertSame('Luis', $result->name);
        $this->assertSame('1988-01-01T00:00:00+00:00', $result->birthdate);
        $this->assertSame('demo', $result->type);
    }
}