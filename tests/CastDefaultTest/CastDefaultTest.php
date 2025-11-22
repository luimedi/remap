<?php

namespace Tests\CastDefaultTest;

use InvalidArgumentException;
use Luimedi\Remap\Mapper;
use PHPUnit\Framework\TestCase;

use Tests\CastDefaultTest\Input;
use Tests\CastDefaultTest\OutputNonStrict;
use Tests\CastDefaultTest\OutputStrict;
use Tests\CastDefaultTest\OutputCasterMissing;

class CastDefaultTest extends TestCase
{
    public function testCastDefaultNonStrictReplacesEmpty()
    {
        $mapper = new Mapper();

        $mapper->bind(Input::class, OutputNonStrict::class);

        $input = new Input(maybe: '');

        $result = $mapper->map($input);

        $this->assertInstanceOf(OutputNonStrict::class, $result);
        $this->assertSame('fallback', $result->maybe);
    }

    public function testCastDefaultStrictOnlyNull()
    {
        $mapper = new Mapper();

        $mapper->bind(Input::class, OutputStrict::class);

        // Empty string should not be replaced when strict=true
        $input = new Input(maybe: '');

        $result = $mapper->map($input);

        $this->assertInstanceOf(OutputStrict::class, $result);
        $this->assertSame('', $result->maybe);

        // Null should be replaced
        $input2 = new Input(maybe: null);
        $result2 = $mapper->map($input2);
        $this->assertSame('fallback', $result2->maybe);
    }

    public function testConstructorCasterThrowsWhenNoValue()
    {
        $this->expectException(InvalidArgumentException::class);

        $mapper = new Mapper();
        $mapper->bind(Input::class, OutputCasterMissing::class);

        $mapper->map(new Input(maybe: null));
    }
}
