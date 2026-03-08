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
}
