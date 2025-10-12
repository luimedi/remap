<?php

namespace Luimedi\Remap;

use InvalidArgumentException;
use Luimedi\Remap\Attribute\MapInterface;
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
        $constructor = $reflectionClass->getConstructor();
        $parameters = $constructor->getParameters();

        $parameterValues = [];

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

        return $reflectionClass->newInstanceArgs($parameterValues);
    }
}