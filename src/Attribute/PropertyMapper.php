<?php

namespace Luimedi\Remap\Attribute;

use Luimedi\Remap\Contracts\CastInterface;
use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MapInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;
use Luimedi\Remap\Contracts\TransformerInterface;
use Luimedi\Remap\Exception\MappingExecutionException;
use Luimedi\Remap\Exception\RemapException;
use Luimedi\Remap\MappingTarget;

#[\Attribute(\Attribute::TARGET_CLASS)]
class PropertyMapper implements TransformerInterface
{
    /**
     * Transforms the given source object into an instance of the target class.
     */
    public function transform(mixed $source, mixed $target, ContextInterface $context, MappingTargetInterface $mappingTarget): mixed
    {
        $reflectionClass = new \ReflectionClass($target);
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
        $instance = is_string($target) ? new $target() : $target;

        foreach ($properties as $property) {
            $value = null;

            $type = null;
            $propType = $property->getType();

            if ($propType && method_exists($propType, 'getName')) {
                $type = $propType->getName();
            }

            $propTarget = new MappingTarget($property->getName(), $type);

            foreach ($this->getValidAttributes($property) as $attribute) {
                if ($attribute instanceof CastInterface) {
                    try {
                        $value = $attribute->cast($value, $context, $propTarget);
                    } catch (\Throwable $exception) {
                        $this->throwWithTrace($exception, $context, [
                            'phase' => 'property.cast',
                            'property' => $property->getName(),
                            'caster' => $attribute::class,
                        ]);
                    }
                } elseif ($attribute instanceof MapInterface) {
                    try {
                        $value = $attribute->map($source, $context, $propTarget);
                    } catch (\Throwable $exception) {
                        $this->throwWithTrace($exception, $context, [
                            'phase' => 'property.map',
                            'property' => $property->getName(),
                            'mapper' => $attribute::class,
                        ]);
                    }
                }
            }

            try {
                $property->setValue($instance, $value);
            } catch (\Throwable $exception) {
                $this->throwWithTrace($exception, $context, [
                    'phase' => 'property.set',
                    'property' => $property->getName(),
                ]);
            }
        }

        return $instance;
    }

    /**
     * Retrieves valid mapping and casting attributes from a property.
     * It sort them so that MapInterface are before CastInterface.
     *
     * @return array<int, MapInterface|CastInterface>
     */
    private function getValidAttributes(\ReflectionProperty $property): array
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
