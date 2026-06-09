<?php

namespace Tests\PropertyMapperTest;

use Luimedi\Remap\Mapper;
use PHPUnit\Framework\TestCase;

class PropertyMapperTest extends TestCase
{
    public function testPropertyMapping()
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, Output::class);

        $input = new Input();
        $input->birthdate = new \DateTime('2000-01-01');

        $output = $mapper->map($input);

        $this->assertInstanceOf(Output::class, $output);
        $this->assertSame('2000-01-01T00:00:00+00:00', $output->birthdate);
    }

    public function testPropertyMappingWithCustomDateFormat()
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, OutputCustomFormat::class);

        $input = new Input();
        $input->birthdate = new \DateTime('2000-01-01 15:30:00');

        $output = $mapper->map($input);

        $this->assertInstanceOf(OutputCustomFormat::class, $output);
        $this->assertSame('2000-01-01', $output->birthdate);
    }
}

#[\Luimedi\Remap\Attribute\PropertyMapper]
class OutputCustomFormat
{
    #[\Luimedi\Remap\Attribute\MapProperty]
    #[\Luimedi\Remap\Attribute\Cast\CastDateTime('Y-m-d')]
    public string $birthdate;
}
