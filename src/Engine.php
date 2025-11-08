<?php

namespace Luimedi\Remap;

use InvalidArgumentException;
use Luimedi\Remap\Attribute\MapInterface;
use Luimedi\Remap\Attribute\TransformerInterface;
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
        $attributes = $reflectionClass->getAttributes();
        $instance = null;

        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();

            if ($attributeInstance instanceof TransformerInterface) {
                $instance = $attributeInstance->transform($from, $type, $context);
            }
        }

        return $instance;
    }
}