<?php

namespace Luimedi\Remap\Attribute;

use Luimedi\Remap\Context;

interface TransformerInterface
{
    /**
     * Transforms the given source object into an instance of the target class.
     */
    public function transform(mixed $source, mixed $target, Context $context): mixed;
}
