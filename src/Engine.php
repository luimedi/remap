<?php

namespace Luimedi\Remap;

use InvalidArgumentException;
use Luimedi\Remap\Attribute\MapInterface;
use Luimedi\Remap\Attribute\TransformerInterface;
use Luimedi\Remap\Cast\CastInterface;
use ReflectionClass;
use ReflectionException;

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
     * @param string|callable($object, Context $context):string $resolver The target type (class name) or a resolver function.
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
     * @throws InvalidArgumentException if no binding is found or cannot be resolved.
     */
    public function resolve(mixed $object, Context $context): string
    {
        $type = get_class($object) ?: 'type:' . gettype($object);

        if (!isset($this->bindings[$type])) {
            throw new InvalidArgumentException("No binding found for {$type}");
        }

        $resolver = $this->bindings[$type];

        if (is_callable($resolver)) {
            return $resolver($object, $context);
        }

        if (class_exists($resolver)) {
            return $resolver;
        }

        throw new InvalidArgumentException(
            "Cannot resolve binding for {$type}");
    }

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