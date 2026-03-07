<?php

namespace Tests\CastDefaultTest;

use Luimedi\Remap\Exception\MissingMappedValueException;
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
        $mapper = new Mapper();
        $mapper->bind(Input::class, OutputCasterMissing::class);

        try {
            $mapper->map(new Input(maybe: null));
            $this->fail('Expected MissingMappedValueException to be thrown');
        } catch (MissingMappedValueException $exception) {
            $trace = $exception->getMappingTrace();

            $this->assertNotEmpty($trace);
            $this->assertSame('execute', $trace[0]['phase'] ?? null);

            $castStep = array_values(array_filter($trace, static function (array $step): bool {
                return ($step['phase'] ?? null) === 'constructor.parameter.cast';
            }));

            $this->assertNotEmpty($castStep);
            $this->assertSame('maybe', $castStep[0]['parameter'] ?? null);
        }
    }
}
