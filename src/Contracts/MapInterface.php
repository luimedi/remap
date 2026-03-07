<?php

namespace Luimedi\Remap\Contracts;

use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;

interface MapInterface
{
    public function map(mixed $from, ContextInterface $context, MappingTargetInterface $target): mixed;
}
