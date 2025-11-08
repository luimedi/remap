<?php

namespace Luimedi\Remap\Attribute\Cast;

interface CastInterface
{
    public function cast(mixed $value): mixed;
}
