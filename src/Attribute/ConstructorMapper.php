<?php

namespace Luimedi\Remap\Attribute;

use InvalidArgumentException;
use Luimedi\Remap\Attribute\Cast\CastInterface;
use Luimedi\Remap\ContextInterface;
use ReflectionClass;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ConstructorMapper implements TransformerInterface
{
    /**
     * Transforms the given source object into an instance of the target class.
     */
    public function transform(mixed $source, mixed $target, ContextInterface $context): mixed
    {
        $reflectionClass = new ReflectionClass($target);
        return $this->newInstance($source, $reflectionClass, $context);
    }

    /**
     * Creates a new instance of the target class by mapping its constructor parameters.
     *
     * @param mixed $from The source object to map from.
     * @param ReflectionClass $reflectionClass The reflection of the target class.
     * @param ContextInterface $context The contextual information for the mapping process.
     * @return mixed A new instance of the target class with mapped parameters.
     * 
     * @throws InvalidArgumentException if a required parameter cannot be mapped.
     */
    private function newInstance(mixed $from, ReflectionClass $reflectionClass, ContextInterface $context): mixed
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
            $this->applyCasters($parameterValues, $parameters, $context));
    }

    /**
     * Applies casters to the parameter values based on their attributes.
     *
     * @param array<string, mixed> $values The current parameter values.
     * @param array<\ReflectionParameter> $parameters The constructor parameters.
     * @param ContextInterface $context The context for the mapping process.
     * 
     * @return array<string, mixed> The parameter values after applying casters.
     * 
     * @throws InvalidArgumentException if a caster is applied to a parameter without a value.
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
                        throw new InvalidArgumentException("Cannot cast parameter '$name' because it has no value.");
                    }
                    $values[$name] = $instance->cast($values[$name], $context);
                }
            }
        }

        return $values;
    }
}
