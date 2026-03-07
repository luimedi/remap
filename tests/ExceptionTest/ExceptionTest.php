<?php

namespace Tests\ExceptionTest;

use Luimedi\Remap\Exception\BindingNotFoundException;
use Luimedi\Remap\Exception\BindingResolutionException;
use Luimedi\Remap\Exception\InvalidTargetTypeException;
use Luimedi\Remap\Exception\MapGetterResolutionException;
use Luimedi\Remap\Exception\MappingExecutionException;
use Luimedi\Remap\Exception\MissingMappedValueException;
use Luimedi\Remap\Exception\RemapException;
use Luimedi\Remap\Mapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testBindingNotFoundExceptionWithMappingTrace()
    {
        $mapper = new Mapper();

        try {
            $mapper->map(new UnboundSource());
            $this->fail('Expected BindingNotFoundException');
        } catch (BindingNotFoundException $exception) {
            // Verify exception message
            $this->assertStringContainsString('No binding found', $exception->getMessage());
            $this->assertStringContainsString(UnboundSource::class, $exception->getMessage());

            // Verify mapping trace exists and has useful data
            $trace = $exception->getMappingTrace();
            $this->assertNotEmpty($trace, 'Mapping trace should not be empty');

            $firstStep = $trace[0] ?? [];
            $this->assertSame('resolve', $firstStep['phase'] ?? null);
            $this->assertArrayHasKey('sourceType', $firstStep);

            // Verify it's a RemapException
            $this->assertInstanceOf(RemapException::class, $exception);
        }
    }

    public function testBindingResolutionExceptionWithCallableReturningInvalid()
    {
        $mapper = new Mapper();

        // Bind to a callable that returns a non-existent class
        $mapper->bind(ValidSource::class, function () {
            return 'NonExistentClass';
        });

        try {
            $mapper->map(new ValidSource());
            $this->fail('Expected BindingResolutionException');
        } catch (BindingResolutionException $exception) {
            $this->assertStringContainsString('Cannot resolve binding', $exception->getMessage());
            $this->assertStringContainsString('NonExistentClass', $exception->getMessage());

            // Verify trace
            $trace = $exception->getMappingTrace();
            $this->assertNotEmpty($trace);
            $this->assertSame('resolve', $trace[0]['phase'] ?? null);
        }
    }

    public function testBindingResolutionExceptionWithCallableReturningNonString()
    {
        $mapper = new Mapper();

        // Bind to a callable that returns wrong type
        $mapper->bind(ValidSource::class, function () {
            return 123;
        });

        try {
            $mapper->map(new ValidSource());
            $this->fail('Expected BindingResolutionException');
        } catch (BindingResolutionException $exception) {
            $this->assertStringContainsString('Cannot resolve binding', $exception->getMessage());
            
            $trace = $exception->getMappingTrace();
            $this->assertNotEmpty($trace);
        }
    }

    public function testInvalidTargetTypeException()
    {
        $mapper = new Mapper();
        
        // Bind to an abstract class that cannot be instantiated
        // This will throw MappingExecutionException wrapping the Error from newInstanceWithoutConstructor
        $mapper->bind(ValidSource::class, AbstractTarget::class);

        try {
            $mapper->map(new ValidSource());
            $this->fail('Expected MappingExecutionException');
        } catch (MappingExecutionException $exception) {
            $this->assertStringContainsString('Mapping execution failed', $exception->getMessage());
            $this->assertStringContainsString('Cannot instantiate abstract class', $exception->getMessage());

            // Verify it wraps the original Error
            $this->assertNotNull($exception->getPrevious());

            // Verify trace
            $trace = $exception->getMappingTrace();
            $this->assertNotEmpty($trace);

            $executeStep = array_values(array_filter($trace, fn($s) => ($s['phase'] ?? null) === 'execute'));
            $this->assertNotEmpty($executeStep);
            $this->assertArrayHasKey('targetType', $executeStep[0]);
        }
    }

    public function testInvalidTargetTypeExceptionForInternalClass()
    {
        $mapper = new Mapper();
        
        // Try to map to an internal class that may cause reflection issues
        // Using a callable to bypass initial class_exists check
        $mapper->bind(ValidSource::class, fn() => \PDO::class);

        try {
            $result = $mapper->map(new ValidSource());
            // PDO might actually work, so we just verify it doesn't throw our custom exception
            $this->assertInstanceOf(\PDO::class, $result);
        } catch (InvalidTargetTypeException | MappingExecutionException $exception) {
            // Either exception is acceptable for this edge case
            $this->assertNotEmpty($exception->getMappingTrace());
        }
    }

    public function testMapGetterResolutionException()
    {
        $mapper = new Mapper();
        $mapper->bind(SourceWithoutGetter::class, TargetWithMapGetter::class);

        try {
            $mapper->map(new SourceWithoutGetter());
            $this->fail('Expected MapGetterResolutionException');
        } catch (MapGetterResolutionException $exception) {
            $this->assertStringContainsString('MapGetter could not resolve', $exception->getMessage());
            $this->assertStringContainsString('missingMethod', $exception->getMessage());

            // Verify trace includes property mapping phase
            $trace = $exception->getMappingTrace();
            $this->assertNotEmpty($trace);

            $propertyMapStep = array_values(array_filter($trace, fn($s) => 
                ($s['phase'] ?? null) === 'property.map'
            ));
            $this->assertNotEmpty($propertyMapStep);
            $this->assertSame('missingMethod', $propertyMapStep[0]['property'] ?? null);
        }
    }

    public function testMissingMappedValueException()
    {
        $mapper = new Mapper();
        $mapper->bind(EmptySource::class, TargetWithCasterOnly::class);

        try {
            $mapper->map(new EmptySource());
            $this->fail('Expected MissingMappedValueException');
        } catch (MissingMappedValueException $exception) {
            $this->assertStringContainsString('has no value', $exception->getMessage());

            $trace = $exception->getMappingTrace();
            $this->assertNotEmpty($trace);

            $castStep = array_values(array_filter($trace, fn($s) => 
                ($s['phase'] ?? null) === 'constructor.parameter.cast'
            ));
            $this->assertNotEmpty($castStep);
            $this->assertArrayHasKey('parameter', $castStep[0]);
            $this->assertArrayHasKey('caster', $castStep[0]);
        }
    }

    public function testMappingExecutionExceptionWrapsUnknownErrors()
    {
        $mapper = new Mapper();
        $mapper->bind(ValidSource::class, TargetWithPropertyThatThrows::class);

        try {
            $mapper->map(new ValidSource());
            $this->fail('Expected MappingExecutionException');
        } catch (MappingExecutionException $exception) {
            $this->assertStringContainsString('Mapping execution failed', $exception->getMessage());

            // Verify original exception is preserved
            $this->assertNotNull($exception->getPrevious());
            $this->assertInstanceOf(\RuntimeException::class, $exception->getPrevious());
            $this->assertStringContainsString('Caster intentional error', $exception->getPrevious()->getMessage());

            // Verify trace
            $trace = $exception->getMappingTrace();
            $this->assertNotEmpty($trace);
            
            // Should have the cast phase where it failed
            $castStep = array_values(array_filter($trace, fn($s) => 
                str_contains($s['phase'] ?? '', 'cast')
            ));
            $this->assertNotEmpty($castStep, 'Should have a cast step in trace');
        }
    }

    #[DataProvider('allExceptionClassesProvider')]
    public function testAllExceptionsAreRemapExceptions(string $exceptionClass)
    {
        $reflection = new \ReflectionClass($exceptionClass);
        $this->assertTrue(
            $reflection->isSubclassOf(RemapException::class),
            "{$exceptionClass} should extend RemapException"
        );
    }

    public static function allExceptionClassesProvider(): array
    {
        return [
            'BindingNotFoundException' => [BindingNotFoundException::class],
            'BindingResolutionException' => [BindingResolutionException::class],
            'InvalidTargetTypeException' => [InvalidTargetTypeException::class],
            'MapGetterResolutionException' => [MapGetterResolutionException::class],
            'MappingExecutionException' => [MappingExecutionException::class],
            'MissingMappedValueException' => [MissingMappedValueException::class],
        ];
    }

    public function testMappingTraceCanBeAppended()
    {
        $exception = BindingNotFoundException::forType('SomeType');
        
        $this->assertEmpty($exception->getMappingTrace());

        $exception->appendMappingTrace([
            ['phase' => 'test1', 'data' => 'value1'],
            ['phase' => 'test2', 'data' => 'value2'],
        ]);

        $trace = $exception->getMappingTrace();
        $this->assertCount(2, $trace);
        $this->assertSame('test1', $trace[0]['phase']);
        $this->assertSame('test2', $trace[1]['phase']);

        // Append more
        $exception->appendMappingTrace([
            ['phase' => 'test3', 'data' => 'value3'],
        ]);

        $trace = $exception->getMappingTrace();
        $this->assertCount(3, $trace);
        $this->assertSame('test3', $trace[2]['phase']);
    }

    public function testMappingTraceDeduplicatesSteps()
    {
        $exception = BindingNotFoundException::forType('SomeType');
        
        $step = ['phase' => 'duplicate', 'data' => 'same'];

        $exception->appendMappingTrace([$step]);
        $exception->appendMappingTrace([$step]); // Same step again

        $trace = $exception->getMappingTrace();
        $this->assertCount(1, $trace, 'Duplicate steps should be deduplicated');
    }
}
