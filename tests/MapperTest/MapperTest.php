<?php

namespace Tests\MapperTest;

use Luimedi\Remap\Mapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    #[DataProvider('mappingInputProvider')]
    public function testGeneralBinding(string $name, \DateTimeImmutable $birthdate, string $expectedDate)
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, Output::class);

        $result = $mapper->map(new Input(name: $name, birthdate: $birthdate));

        $this->assertInstanceOf(Output::class, $result);
        $this->assertSame($name, $result->name);
        $this->assertSame($expectedDate, $result->birthdate);
        $this->assertSame('demo', $result->type);
    }

    public static function mappingInputProvider(): array
    {
        return [
            'basic date' => ['Luis', new \DateTimeImmutable('1988-01-01'), '1988-01-01T00:00:00+00:00'],
            'with time' => ['Ana', new \DateTimeImmutable('1990-05-15 14:30:00'), '1990-05-15T14:30:00+00:00'],
            'leap year' => ['Pedro', new \DateTimeImmutable('2020-02-29'), '2020-02-29T00:00:00+00:00'],
            'end of year' => ['Maria', new \DateTimeImmutable('2025-12-31 23:59:59'), '2025-12-31T23:59:59+00:00'],
        ];
    }

    #[DataProvider('iterableMappingProvider')]
    public function testIterableMapping(array $inputs, int $expectedCount)
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, Output::class);

        $results = $mapper->mapAsIterable($inputs);

        $this->assertIsArray($results);
        $this->assertCount($expectedCount, $results);

        foreach ($results as $result) {
            $this->assertInstanceOf(Output::class, $result);
            $this->assertNotEmpty($result->name);
            $this->assertNotEmpty($result->birthdate);
        }

        // Verify first item specifically
        if ($expectedCount > 0) {
            $this->assertSame($inputs[0]->name, $results[0]->name);
        }
    }

    public static function iterableMappingProvider(): array
    {
        return [
            'two items' => [
                [
                    new Input(name: 'Luis', birthdate: new \DateTimeImmutable('1988-01-01')),
                    new Input(name: 'Ana', birthdate: new \DateTimeImmutable('1990-05-15')),
                ],
                2,
            ],
            'single item' => [
                [new Input(name: 'Solo', birthdate: new \DateTimeImmutable('2000-01-01'))],
                1,
            ],
            'empty array' => [[], 0],
            'three items' => [
                [
                    new Input(name: 'First', birthdate: new \DateTimeImmutable('2020-01-01')),
                    new Input(name: 'Second', birthdate: new \DateTimeImmutable('2021-02-02')),
                    new Input(name: 'Third', birthdate: new \DateTimeImmutable('2022-03-03')),
                ],
                3,
            ],
        ];
    }

    public function testCastIterable()
    {
        $mapper = new Mapper();

        $mapper
            ->bind(SecondaryInput::class, SecondaryOutput::class);

        $input = new SecondaryInput(dates: [
            new \DateTimeImmutable('2020-01-01'),
            new \DateTimeImmutable('2021-02-02'),
            new \DateTimeImmutable('2022-03-03'),
        ]);

        $result = $mapper->map($input);

        $this->assertInstanceOf(SecondaryOutput::class, $result);
        $this->assertIsArray($result->dates);
        $this->assertCount(3, $result->dates);

        $this->assertSame('2020-01-01T00:00:00+00:00', $result->dates[0]);
        $this->assertSame('2021-02-02T00:00:00+00:00', $result->dates[1]);
        $this->assertSame('2022-03-03T00:00:00+00:00', $result->dates[2]);
    }

    public function testCastIterableThrowsOnNonIterable()
    {
        $mapper = new Mapper();
        $mapper->bind(SecondaryInputLoose::class, OutputNonIterable::class);

        $input = new SecondaryInputLoose(dates: 'not-an-iterable');

        $this->expectException(\Luimedi\Remap\Exception\MappingExecutionException::class);
        $this->expectExceptionMessage('Value must be iterable to be cast as iterable.');
        $mapper->map($input);
    }

    public function testCastIterableThrowsOnInvalidCaster()
    {
        $mapper = new Mapper();
        $mapper->bind(SecondaryInput::class, OutputInvalidCaster::class);

        $input = new SecondaryInput(dates: [new \DateTimeImmutable()]);

        $this->expectException(\Luimedi\Remap\Exception\MappingExecutionException::class);
        $this->expectExceptionMessage('must implement Luimedi\Remap\Contracts\CastInterface');
        $mapper->map($input);
    }
}

class SecondaryInputLoose
{
    public function __construct(public $dates)
    {
    }
}

#[\Luimedi\Remap\Attribute\ConstructorMapper]
class OutputNonIterable
{
    public function __construct(
        #[\Luimedi\Remap\Attribute\MapProperty]
        #[\Luimedi\Remap\Attribute\Cast\CastIterable(class: \Luimedi\Remap\Attribute\Cast\CastDateTime::class)]
        public array $dates,
    ) {
    }
}

#[\Luimedi\Remap\Attribute\ConstructorMapper]
class OutputInvalidCaster
{
    public function __construct(
        #[\Luimedi\Remap\Attribute\MapProperty]
        #[\Luimedi\Remap\Attribute\Cast\CastIterable(class: \stdClass::class)]
        public array $dates,
    ) {
    }
}
