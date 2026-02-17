<?php

namespace Luimedi\Remap\Attribute;

use Luimedi\Remap\ContextInterface;
use Luimedi\Remap\MappingTarget;

interface MapInterface
{
    public function map(mixed $from, ContextInterface $context, MappingTarget $target): mixed;
}
