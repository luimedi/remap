<?php

namespace Luimedi\Remap;

use InvalidArgumentException;
use Luimedi\Remap\Attribute\MapInterface;
use Luimedi\Remap\Cast\CastInterface;
use ReflectionClass;
use ReflectionException;

class Engine
{
    /**
     * Executes the mapping process from the source object to an instance of the target type.
     * 
     * @throws ReflectionException if the target type class does not exist
     */
    public function execute(mixed $from, string $type, Context $context): mixed
    {
        $reflectionClass = new ReflectionClass($type);
        $instance = $this->newInstance($from, $reflectionClass, $context);

        return $instance;
    }

    /**
     * Creates a new instance of the target class by mapping its constructor parameters.
     *
     * @param mixed $from The source object to map from.
     * @param ReflectionClass $reflectionClass The reflection of the target class.
     * @param Context $context The contextual information for the mapping process.
     * @return mixed A new instance of the target class with mapped parameters.
     * 
     * @throws InvalidArgumentException if a required parameter cannot be mapped.
     */
    private function newInstance(mixed $from, ReflectionClass $reflectionClass, Context $context): mixed
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
                    $parameterValues[$name] = $instance->map($from, $context);
                }
            }
        };

        return $reflectionClass->newInstanceArgs(
            $this->applyCasters($parameterValues, $parameters));
    }

    /**
     * Applies casters to the parameter values based on their attributes.
     *
     * @param array<string, mixed> $values The current parameter values.
     * @param array<\ReflectionParameter> $parameters The constructor parameters.
     * @return array<string, mixed> The parameter values after applying casters.
     * 
     * @throws InvalidArgumentException if a caster is applied to a parameter without a value.
     */
    protected function applyCasters(array $values, array $parameters): array
    {
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $attributes = $parameter->getAttributes();

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                
                if ($instance instanceof CastInterface) {
                    if (!array_key_exists($name, $values)) {
                        throw new InvalidArgumentException("Cannot cast parameter '$name' because it has no value.");
                    }
                    $values[$name] = $instance->cast($values[$name]);
                }
            }
        }

        return $values;
    }
}