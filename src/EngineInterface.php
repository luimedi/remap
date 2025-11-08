<?php

namespace Luimedi\Remap;

interface EngineInterface
{
    /**
     * Binds a source type to a target type or a resolver function.
     * 
     * @param string $abstract The source type (class name or 'type:<type>').
     * @param string|callable($object, Context $context):string $resolver The target type (class name) or a resolver function.
     * 
     * @return $this
     */
    public function bind(string $abstract, string|callable $resolver): static;

    /**
     * Resolves the target type for the given object.
     * 
     * @throws InvalidArgumentException if no binding is found or cannot be resolved.
     */
    public function resolve(mixed $object, Context $context): string;

    /**
     * Executes the mapping from the source object to an instance of the target type.
     */
    public function execute(mixed $from, string $type, Context $context): mixed;
}
