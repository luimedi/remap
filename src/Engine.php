<?php

namespace Luimedi\Remap;

use InvalidArgumentException;
use Luimedi\Remap\Attribute\MapInterface;
use Luimedi\Remap\Cast\CastInterface;
use ReflectionClass;
use ReflectionException;

class Engine
{
    protected array $attributes = [];
    protected array $casters = [];

    /**
     * @param array<class-string<\Attribute>> $attributes
     * @param array<class-string<Caster>> $casters
     */
    public function __construct(array $attributes = [], array $casters = [])
    {
        $this->attributes = $attributes;
        $this->casters = $casters;
    }

    /**
     * @throws ReflectionException if the target type class does not exist
     */
    public function execute(mixed $from, string $type, Context $context): mixed
    {
        $reflectionClass = new ReflectionClass($type);
        $instance = $this->newInstance($from, $reflectionClass, $context);

        return $instance;
    }

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
                if (!in_array($attribute->getName(), $this->attributes, true)) {
                    continue;
                }

                $instance = $attribute->newInstance();
                
                if ($instance instanceof MapInterface) {
                    $parameterValues[$name] = $instance->map($from, $context);
                }
            }
        }

        // Now let's apply any casters if needed
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $attributes = $parameter->getAttributes();

            foreach ($attributes as $attribute) {
                if (!in_array($attribute->getName(), $this->casters, true)) {
                    continue;
                }

                $instance = $attribute->newInstance();
                
                if ($instance instanceof CastInterface) {
                    if (!array_key_exists($name, $parameterValues)) {
                        throw new InvalidArgumentException("Cannot cast parameter '$name' because it has no value.");
                    }
                    $parameterValues[$name] = $instance->cast($parameterValues[$name]);
                }
            }
        }

        return $reflectionClass->newInstanceArgs($parameterValues);
    }
}