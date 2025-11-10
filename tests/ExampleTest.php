<?php

namespace Tests;

use DateTimeImmutable;
use Luimedi\Remap\Mapper;
use PHPUnit\Framework\TestCase;
use Tests\Demo\Input;
use Tests\Demo\NestedOutput;
use Tests\Demo\Output;

class ExampleTest extends TestCase
{
    public function testGeneralBinding()
    {
        $mapper = new Mapper();
        
        $mapper
            ->bind(Input::class, Output::class)
            ->bind('type:array', NestedOutput::class);

        $result = $mapper->map(new Input(name: 'Luis', birthdate: new DateTimeImmutable('1988-01-01')));
        $this->assertInstanceOf(Output::class, $result);

        $this->assertSame('Luis', $result->name);
        $this->assertSame('1988-01-01T00:00:00+00:00', $result->birthdate);
        $this->assertSame('demo', $result->type);
    }

    public function testIterableMapping()
    {
        $mapper = new Mapper();
        
        $mapper
            ->bind(Input::class, Output::class)
            ->bind('type:array', NestedOutput::class);

        $inputs = [
            new Input(name: 'Luis', birthdate: new DateTimeImmutable('1988-01-01')),
            new Input(name: 'Ana', birthdate: new DateTimeImmutable('1990-05-15')),
        ];

        $iterator = $mapper->mapAsIterator($inputs);

        $this->assertInstanceOf(\Iterator::class, $iterator);

        $results = iterator_to_array($iterator);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(Output::class, $results[0]);
        $this->assertInstanceOf(Output::class, $results[1]);

        $this->assertSame('Luis', $results[0]->name);
        $this->assertSame('1988-01-01T00:00:00+00:00', $results[0]->birthdate);

        $this->assertSame('Ana', $results[1]->name);
        $this->assertSame('1990-05-15T00:00:00+00:00', $results[1]->birthdate);
    }
}