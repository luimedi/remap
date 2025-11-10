<?php

namespace Tests;

use DateTime;
use Luimedi\Remap\Mapper;
use PHPUnit\Framework\TestCase;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\PropertyMapper;
use Luimedi\Remap\Attribute\Cast\CastDateTime;

class Input
{
    public DateTime $birthdate;
}

#[PropertyMapper]
class Output
{
    #[MapProperty(source: 'birthdate')]
    #[CastDateTime]
    public string $birthdate;
}

class PropertyMapperTest extends TestCase
{
    public function testPropertyMapping()
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, Output::class);

        $input = new Input();
        $input->birthdate = new DateTime('2000-01-01');

        $output = $mapper->map($input);

        $this->assertInstanceOf(Output::class, $output);
        $this->assertSame('2000-01-01T00:00:00+00:00', $output->birthdate);
    }
}
