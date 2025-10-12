<?php

namespace Luimedi\Remap\Attribute;

use Luimedi\Remap\Context;

interface MapInterface
{
    public function map(mixed $from, Context $context): mixed;
}
