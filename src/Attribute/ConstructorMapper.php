<?php

namespace Luimedi\Remap\Attribute;

use Luimedi\Remap\Contracts\CastInterface;
use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MapInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;
use Luimedi\Remap\Contracts\TransformerInterface;
use Luimedi\Remap\Exception\MappingExecutionException;
use Luimedi\Remap\Exception\MissingMappedValueException;
use Luimedi\Remap\Exception\RemapException;
use Luimedi\Remap\MappingTarget;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ConstructorMapper implements TransformerInterface
{
    /**
     * Transforms the given source object into an instance of the target class.
     */
    public function transform(mixed $source, mixed $target, ContextInterface $context, MappingTargetInterface $mappingTarget): mixed
    {
        $reflectionClass = new \ReflectionClass($target);

        // If transform is invoked with an existing target object, populate it
        // rather than creating a new instance. This allows Engine to pre-register
        // placeholder instances for recursive mappings.
        if (is_object($target)) {
            return $this->populateInstance($source, $reflectionClass, $target, $context);
        }

        return $this->newInstance($source, $reflectionClass, $context);
    }

    /**
     * Creates a new instance of the target class by mapping its constructor parameters.
     *
     * @param mixed            $from            the source object to map from
     * @param \ReflectionClass $reflectionClass the reflection of the target class
     * @param ContextInterface $context         the contextual information for the mapping process
     *
     * @return mixed a new instance of the target class with mapped parameters
     */
    private function newInstance(mixed $from, \ReflectionClass $reflectionClass, ContextInterface $context): mixed
    {
        $constructor = $reflectionClass->getConstructor();
        $parameters = $constructor->getParameters();

        $parameterValues = [];

        // Iterate over each parameter of the constructor and apply the appropriate mapping
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $attributes = $parameter->getAttributes();

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof MapInterface) {
                    $type = null;
                    $paramType = $parameter->getType();
                    if ($paramType && method_exists($paramType, 'getName')) {
                        $type = $paramType->getName();
                    }
                    $target = new MappingTarget($name, $type);

                    try {
                        $parameterValues[$name] = $instance->map($from, $context, $target);
                    } catch (\Throwable $exception) {
                        $this->throwWithTrace($exception, $context, [
                            'phase' => 'constructor.parameter.map',
                            'parameter' => $name,
                            'mapper' => $instance::class,
                        ]);
                    }
                }
            }
        }

        return $reflectionClass->newInstanceArgs(
            $this->applyCasters($parameterValues, $parameters, $context));
    }

    /**
     * Populate an existing instance (created without constructor) with mapped values.
     */
    private function populateInstance(mixed $from, \ReflectionClass $reflectionClass, object $instance, ContextInterface $context): mixed
    {
        $constructor = $reflectionClass->getConstructor();
        $parameters = $constructor->getParameters();

        $parameterValues = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $attributes = $parameter->getAttributes();

            foreach ($attributes as $attribute) {
                $attrInstance = $attribute->newInstance();

                if ($attrInstance instanceof MapInterface) {
                    $paramType = $parameter->getType();
                    $type = null;
                    if ($paramType && method_exists($paramType, 'getName')) {
                        $type = $paramType->getName();
                    }
                    $target = new MappingTarget($name, $type);

                    try {
                        $parameterValues[$name] = $attrInstance->map($from, $context, $target);
                    } catch (\Throwable $exception) {
                        $this->throwWithTrace($exception, $context, [
                            'phase' => 'constructor.parameter.map',
                            'parameter' => $name,
                            'mapper' => $attrInstance::class,
                        ]);
                    }
                }
            }
        }

        $parameterValues = $this->applyCasters($parameterValues, $parameters, $context);

        // Set each parameter value on the instance. For non-public properties
        // use a bound closure instead of ReflectionProperty::setAccessible
        // (deprecated).
        foreach ($parameterValues as $name => $value) {
            if ($reflectionClass->hasProperty($name)) {
                $prop = $reflectionClass->getProperty($name);

                if ($prop->isPublic()) {
                    $prop->setValue($instance, $value);
                } else {
                    // Use a closure bound to the target class to set non-public props.
                    $setter = function ($val) use ($name) {
                        $this->{$name} = $val;
                    };

                    $bound = $setter->bindTo($instance, $reflectionClass->getName());
                    $bound($value);
                }
            } else {
                $instance->$name = $value;
            }
        }

        return $instance;
    }

    /**
     * Applies casters to the parameter values based on their attributes.
     *
     * @param array<string, mixed>        $values     the current parameter values
     * @param array<\ReflectionParameter> $parameters the constructor parameters
     * @param ContextInterface            $context    the context for the mapping process
     *
     * @return array<string, mixed> the parameter values after applying casters
     */
    protected function applyCasters(array $values, array $parameters, ContextInterface $context): array
    {
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $attributes = $parameter->getAttributes();

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof CastInterface) {
                    if (!array_key_exists($name, $values)) {
                        $this->throwWithTrace(
                            MissingMappedValueException::forParameter($name),
                            $context,
                            [
                                'phase' => 'constructor.parameter.cast',
                                'parameter' => $name,
                                'caster' => $instance::class,
                            ]
                        );
                    }

                    $paramType = $parameter->getType();
                    $type = null;

                    if ($paramType && method_exists($paramType, 'getName')) {
                        $type = $paramType->getName();
                    }

                    $target = new MappingTarget($name, $type);

                    try {
                        $values[$name] = $instance->cast($values[$name], $context, $target);
                    } catch (\Throwable $exception) {
                        $this->throwWithTrace($exception, $context, [
                            'phase' => 'constructor.parameter.cast',
                            'parameter' => $name,
                            'caster' => $instance::class,
                        ]);
                    }
                }
            }
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $step
     */
    private function throwWithTrace(\Throwable $exception, ContextInterface $context, array $step): never
    {
        $trace = $context->get('__mapping_trace__', []);
        $trace[] = $step;

        if ($exception instanceof RemapException) {
            throw $exception->appendMappingTrace($trace);
        }

        throw MappingExecutionException::fromThrowable($exception, $trace);
    }
}
