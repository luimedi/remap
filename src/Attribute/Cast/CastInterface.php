<?php

namespace Luimedi\Remap\Attribute\Cast;

use Luimedi\Remap\ContextInterface;
use Luimedi\Remap\MappingTarget;

interface CastInterface
{
    public function cast(mixed $value, ContextInterface $context, MappingTarget $mappingTarget): mixed;
}
