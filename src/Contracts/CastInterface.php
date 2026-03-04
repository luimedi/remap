<?php

namespace Luimedi\Remap\Contracts;

use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\MappingTarget;

interface CastInterface
{
    public function cast(mixed $value, ContextInterface $context, MappingTarget $mappingTarget): mixed;
}
