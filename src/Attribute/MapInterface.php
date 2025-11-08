<?php

namespace Luimedi\Remap\Attribute;

use Luimedi\Remap\ContextInterface;

interface MapInterface
{
    public function map(mixed $from, ContextInterface $context): mixed;
}
