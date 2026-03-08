<?php

namespace Luimedi\Remap\Contracts;

interface TransformerInterface
{
    /**
     * Transforms the given source object into an instance of the target class.
     */
    public function transform(mixed $source, mixed $target, ContextInterface $context, MappingTargetInterface $mappingTarget): mixed;
}
