<?php

namespace Luimedi\Remap\Cast;

interface CastInterface
{
    public function cast(mixed $value): mixed;
}
