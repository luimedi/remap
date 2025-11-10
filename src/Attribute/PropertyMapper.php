<?php

namespace Luimedi\Remap\Attribute;

use InvalidArgumentException;
use Luimedi\Remap\Attribute\Cast\CastInterface;
use Luimedi\Remap\ContextInterface;
use ReflectionClass;
use ReflectionProperty;

#[\Attribute(\Attribute::TARGET_CLASS)]
class PropertyMapper implements TransformerInterface
{
    /**
     * Transforms the given source object into an instance of the target class.
     */
    public function transform(mixed $source, mixed $target, ContextInterface $context): mixed
    {
        $reflectionClass = new ReflectionClass($target);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
        $instance = is_string($target) ? new $target() : $target;

        foreach ($properties as $property) {
            $value = null;

            foreach ($this->getValidAttributes($property) as $attribute) {
                if ($attribute instanceof CastInterface) {
                    $value = $attribute->cast($value, $context);
                } elseif ($attribute instanceof MapInterface) {
                    $value = $attribute->map($source, $context);
                }
            }

            $property->setValue($instance, $value);
        }

        return $instance;
    }

    /**
     * Retrieves valid mapping and casting attributes from a property.
     * It sort them so that MapInterface are before CastInterface.
     * 
     * @return array<int, MapInterface|CastInterface>
     */
    private function getValidAttributes(ReflectionProperty $property): array
    {
        $validAttributes = [];
        $attributes = $property->getAttributes();

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            if (
                $instance instanceof MapInterface 
                || $instance instanceof CastInterface
            ) {
                $validAttributes[] = $instance;
            }
        }

        usort($validAttributes, function ($a, $b) {
            if ($a instanceof CastInterface && $b instanceof MapInterface) {
                return 1; // Casts after Maps
            } elseif ($a instanceof MapInterface && $b instanceof CastInterface) {
                return -1; // Maps before Casts
            }
            return 0;
        });

        return $validAttributes;
    }
}
