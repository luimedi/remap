<?php

namespace Luimedi\Remap\Attribute;

use Luimedi\Remap\ContextInterface;

interface TransformerInterface
{
    /**
     * Transforms the given source object into an instance of the target class.
     */
    public function transform(mixed $source, mixed $target, ContextInterface $context): mixed;
}
