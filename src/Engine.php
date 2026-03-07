<?php

namespace Luimedi\Remap;

use Luimedi\Remap\Exception\BindingNotFoundException;
use Luimedi\Remap\Exception\BindingResolutionException;
use Luimedi\Remap\Exception\InvalidTargetTypeException;
use Luimedi\Remap\Exception\MappingExecutionException;
use Luimedi\Remap\Exception\RemapException;
use Luimedi\Remap\Contracts\EngineInterface;
use Luimedi\Remap\Contracts\TransformerInterface;
use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\MappingTarget;
use ReflectionClass;
use ReflectionException;
use Throwable;

class Engine implements EngineInterface
{
    /**
     * @var array<string, string|callable>
     */
    protected array $bindings = [];

    /**
     * Binds a source type to a target type or a resolver function.
     * 
     * @param string $abstract The source type (class name or 'type:<type>').
     * @param string|callable($object, ContextInterface $context):string $resolver The target type (class name) or a resolver function.
     * 
     * @return $this
     */
    public function bind(string $abstract, string|callable $resolver): static
    {
        $this->bindings[$abstract] = $resolver;
        return $this;
    }

    /**
     * Resolves the target type for the given object.
     * 
     * @throws BindingNotFoundException if no binding is found.
     * @throws BindingResolutionException if a binding cannot be resolved to a valid class name.
     */
    public function resolve(mixed $object, ContextInterface $context): string
    {
        $type = is_object($object) ? get_class($object) : 'type:' . gettype($object);
        $trace = [[
            'phase' => 'resolve',
            'sourceType' => $type,
        ]];

        if (!isset($this->bindings[$type])) {
            throw BindingNotFoundException::forType($type)->appendMappingTrace($trace);
        }

        $resolver = $this->bindings[$type];

        if (is_callable($resolver)) {
            $resolvedType = $resolver($object, $context);

            if (is_string($resolvedType) && class_exists($resolvedType)) {
                return $resolvedType;
            }

            throw BindingResolutionException::forType($type, $resolvedType)
                ->appendMappingTrace($trace);
        }

        if (is_string($resolver) && class_exists($resolver)) {
            return $resolver;
        }

        throw BindingResolutionException::forType($type, $resolver)->appendMappingTrace($trace);
    }

    /**
     * Executes the mapping process from the source object to an instance of the target type.
     * 
     * @throws InvalidTargetTypeException if the target type cannot be instantiated.
     */
    public function execute(mixed $from, string $type, ContextInterface $context): mixed
    {
        return $this->withMappingStep($context, [
            'phase' => 'execute',
            'targetType' => $type,
            'sourceType' => is_object($from) ? get_class($from) : gettype($from),
        ], function () use ($from, $type, $context) {
            try {
                $reflectionClass = new ReflectionClass($type);
            } catch (ReflectionException $exception) {
                throw InvalidTargetTypeException::forType($type, $exception);
            }

            $attributes = $reflectionClass->getAttributes();

            $instance = null;

        // If the source is an object, prepare a registry mapping so recursive
        // references can return the already-created target instance.
            if (is_object($from)) {
                $id = spl_object_hash($from);
                $registry = $context->get('__mapping_registry__', []);

                if (isset($registry[$id])) {
                    // There is already a mapped instance for this source.
                    $instance = $registry[$id];
                } else {
                    // Create a placeholder instance without invoking constructor so
                    // it can be returned for recursive references during mapping.
                    try {
                        $instance = $reflectionClass->newInstanceWithoutConstructor();
                    } catch (ReflectionException $exception) {
                        throw InvalidTargetTypeException::forType($type, $exception);
                    }

                    $registry[$id] = $instance;
                    $context->set('__mapping_registry__', $registry);
                }
            }

            foreach ($attributes as $attribute) {
                $attributeClass = $attribute->getName();

                $attributeInstance = $this->withMappingStep($context, [
                    'phase' => 'attribute.instantiate',
                    'targetType' => $type,
                    'attribute' => $attributeClass,
                ], static function () use ($attribute) {
                    return $attribute->newInstance();
                });

                if ($attributeInstance instanceof TransformerInterface) {
                    $mappingTarget = new MappingTarget($reflectionClass->getName(), $type);

                    $instance = $this->withMappingStep($context, [
                        'phase' => 'attribute.transform',
                        'targetType' => $type,
                        'attribute' => $attributeClass,
                    ], static function () use ($attributeInstance, $from, $instance, $type, $context, $mappingTarget) {
                        return $attributeInstance->transform($from, $instance ?? $type, $context, $mappingTarget);
                    });
                }
            }

            // Ensure registry points to the final instance if source was object.
            if (is_object($from)) {
                $id = spl_object_hash($from);
                $registry = $context->get('__mapping_registry__', []);
                $registry[$id] = $instance;
                $context->set('__mapping_registry__', $registry);
            }

            return $instance;
        });
    }

    /**
     * @param array<string, mixed> $step
     */
    private function withMappingStep(ContextInterface $context, array $step, callable $callback): mixed
    {
        $trace = $context->get('__mapping_trace__', []);
        $trace[] = $step;
        $context->set('__mapping_trace__', $trace);

        try {
            return $callback();
        } catch (Throwable $exception) {
            throw $this->enrichException($exception, $trace);
        } finally {
            $finalTrace = $context->get('__mapping_trace__', []);
            array_pop($finalTrace);
            $context->set('__mapping_trace__', $finalTrace);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $trace
     */
    private function enrichException(Throwable $exception, array $trace): RemapException
    {
        if ($exception instanceof RemapException) {
            return $exception->appendMappingTrace($trace);
        }

        return MappingExecutionException::fromThrowable($exception, $trace);
    }
}