<?php

namespace Tests\EngineTest;

use Luimedi\Remap\Exception\BindingNotFoundException;
use Luimedi\Remap\Exception\RemapException;
use Luimedi\Remap\Mapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
    #[DataProvider('callableResolverProvider')]
    public function testCallableResolverIsUsed(callable $resolver, string $expectedClass)
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, $resolver);

        $result = $mapper->map(new Input());

        $this->assertInstanceOf($expectedClass, $result);
    }

    public static function callableResolverProvider(): array
    {
        return [
            'simple resolver' => [
                fn($obj, $ctx) => Output::class,
                Output::class,
            ],
            'context-aware resolver' => [
                function ($obj, $ctx) {
                    // Could check context for routing logic
                    return Output::class;
                },
                Output::class,
            ],
        ];
    }

    public function testResolveThrowsWhenNoBinding()
    {
        $mapper = new Mapper();

        try {
            $mapper->map(new class {});
            $this->fail('Expected BindingNotFoundException');
        } catch (BindingNotFoundException $exception) {
            // Verify exception details
            $this->assertStringContainsString('No binding found', $exception->getMessage());
            
            // Verify mapping trace
            $trace = $exception->getMappingTrace();
            $this->assertNotEmpty($trace);
            $this->assertSame('resolve', $trace[0]['phase'] ?? null);
            $this->assertArrayHasKey('sourceType', $trace[0]);
        }
    }

    public function testLibraryExceptionsCanBeCaughtByBaseRemapException()
    {
        $mapper = new Mapper();

        try {
            $mapper->map(new class {});
            $this->fail('Expected an exception to be thrown');
        } catch (RemapException $exception) {
            $this->assertInstanceOf(RemapException::class, $exception);
            $this->assertInstanceOf(BindingNotFoundException::class, $exception);
            
            // Verify we can get trace and previous exception info
            $this->assertIsArray($exception->getMappingTrace());
        }
    }

    public function testContextPreservationAcrossMappings()
    {
        $mapper = new Mapper();
        $mapper->bind(Input::class, Output::class);
        $mapper->withContext('test_key', 'test_value');

        $result = $mapper->map(new Input());

        $this->assertInstanceOf(Output::class, $result);
        
        // Context should be preserved in the mapper
        $context = $mapper->getContext();
        $this->assertSame('test_value', $context->get('test_key'));
    }
}
