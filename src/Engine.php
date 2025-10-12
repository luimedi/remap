<?php

namespace Luimedi\Remap;

use InvalidArgumentException;
use ReflectionClass;

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

    public function execute(mixed $from, string $type, Context $context): mixed
    {
        if (!class_exists($type)) {
            throw new InvalidArgumentException("Target class $type does not exist.");
        }

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
                $parameterValues[$name] = $instance->map($from, $context);
            }
        }

        return $reflectionClass->newInstanceArgs($parameterValues);
    }
}