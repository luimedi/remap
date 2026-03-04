<?php

namespace Luimedi\Remap\Contracts;

use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\MappingTarget;

interface MapInterface
{
    public function map(mixed $from, ContextInterface $context, MappingTarget $target): mixed;
}
