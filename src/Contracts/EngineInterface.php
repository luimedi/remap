<?php

namespace Luimedi\Remap\Contracts;

use Luimedi\Remap\Exception\BindingNotFoundException;
use Luimedi\Remap\Exception\BindingResolutionException;
use Luimedi\Remap\Exception\InvalidTargetTypeException;

interface EngineInterface
{
    /**
     * Binds a source type to a target type or a resolver function.
     *
     * @param string $abstract the source type (class name or 'type:<type>')
     * @param string|callable($object, ContextInterface $context):string $resolver The target type (class name) or a resolver function
     *
     * @return $this
     */
    public function bind(string $abstract, string|callable $resolver): static;

    /**
     * Resolves the target type for the given object.
     *
     * @throws BindingNotFoundException   if no binding is found
     * @throws BindingResolutionException if a binding cannot be resolved
     */
    public function resolve(mixed $object, ContextInterface $context): string;

    /**
     * Executes the mapping from the source object to an instance of the target type.
     *
     * @throws InvalidTargetTypeException if the target type cannot be instantiated
     */
    public function execute(mixed $from, string $type, ContextInterface $context): mixed;
}
