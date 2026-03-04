<?php

namespace Luimedi\Remap\Contracts;

use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;

interface CastInterface
{
    public function cast(mixed $value, ContextInterface $context, MappingTargetInterface $mappingTarget): mixed;
}
