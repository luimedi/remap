<?php

namespace Tests\MapperTest;

use DateTimeImmutable;
use Luimedi\Remap\Mapper;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
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

        $results = $mapper->mapAsIterable($inputs);
        $this->assertIsArray($results);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(Output::class, $results[0]);
        $this->assertInstanceOf(Output::class, $results[1]);

        $this->assertSame('Luis', $results[0]->name);
        $this->assertSame('1988-01-01T00:00:00+00:00', $results[0]->birthdate);

        $this->assertSame('Ana', $results[1]->name);
        $this->assertSame('1990-05-15T00:00:00+00:00', $results[1]->birthdate);
    }

    public function testCastIterable()
    {
        $mapper = new Mapper();
        
        $mapper
            ->bind(SecondaryInput::class, SecondaryOutput::class);

        $input = new SecondaryInput(dates: [
            new DateTimeImmutable('2020-01-01'),
            new DateTimeImmutable('2021-02-02'),
            new DateTimeImmutable('2022-03-03'),
        ]);

        $result = $mapper->map($input);

        $this->assertInstanceOf(SecondaryOutput::class, $result);
        $this->assertIsArray($result->dates);
        $this->assertCount(3, $result->dates);
        
        $this->assertSame('2020-01-01T00:00:00+00:00', $result->dates[0]);
        $this->assertSame('2021-02-02T00:00:00+00:00', $result->dates[1]);
        $this->assertSame('2022-03-03T00:00:00+00:00', $result->dates[2]);
    }
}