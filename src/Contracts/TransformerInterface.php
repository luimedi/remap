<?php

namespace Luimedi\Remap\Contracts;

use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\MappingTarget;

interface TransformerInterface
{
    /**
     * Transforms the given source object into an instance of the target class.
     */
    public function transform(mixed $source, mixed $target, ContextInterface $context, MappingTarget $mappingTarget): mixed;
}
