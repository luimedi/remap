<?php

namespace Tests\CastDefaultTest;

use Luimedi\Remap\Exception\MissingMappedValueException;
use Luimedi\Remap\Mapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CastDefaultTest extends TestCase
{
    #[DataProvider('nonStrictEmptyValuesProvider')]
    public function testCastDefaultNonStrictReplacesEmpty(mixed $emptyValue, string $expectedReplacement)
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, OutputNonStrict::class);

        $input = new Input(maybe: $emptyValue);
        $result = $mapper->map($input);

        $this->assertInstanceOf(OutputNonStrict::class, $result);
        $this->assertSame($expectedReplacement, $result->maybe);
    }

    public static function nonStrictEmptyValuesProvider(): array
    {
        return [
            'empty string' => ['', 'fallback'],
            'null value' => [null, 'fallback'],
            'zero integer' => [0, 'fallback'],
            'false boolean' => [false, 'fallback'],
        ];
    }

    #[DataProvider('strictModeValuesProvider')]
    public function testCastDefaultStrictOnlyNull(mixed $inputValue, mixed $expectedOutput)
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, OutputStrict::class);

        $input = new Input(maybe: $inputValue);
        $result = $mapper->map($input);

        $this->assertInstanceOf(OutputStrict::class, $result);
        $this->assertSame($expectedOutput, $result->maybe);
    }

    public static function strictModeValuesProvider(): array
    {
        return [
            'empty string not replaced' => ['', ''],
            'zero not replaced' => [0, 0],
            'false not replaced' => [false, false],
            'null is replaced' => [null, 'fallback'],
        ];
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
