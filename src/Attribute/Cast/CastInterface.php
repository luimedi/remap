<?php

namespace Luimedi\Remap\Attribute\Cast;

use Luimedi\Remap\ContextInterface;

interface CastInterface
{
    public function cast(mixed $value, ContextInterface $context): mixed;
}
