<?php

namespace Tests\ExceptionTest;

use Attribute;
use Luimedi\Remap\Contracts\CastInterface;
use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class ThrowingCaster implements CastInterface
{
    public function cast(mixed $value, ContextInterface $context, MappingTargetInterface $target): mixed
    {
        throw new \RuntimeException('Caster intentional error');
    }
}
