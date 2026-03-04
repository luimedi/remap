<?php

namespace Luimedi\Remap\Attribute;

use Attribute;
use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MapInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;
use Luimedi\Remap\Data;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class MapProperty implements MapInterface
{
    public function __construct(protected ?string $source = null)
    {
        //
    }

    public function map(mixed $from, ContextInterface $context, MappingTargetInterface $target): mixed
    {
        return Data::get($from, $this->source ?? $target->getName());
    }
}
