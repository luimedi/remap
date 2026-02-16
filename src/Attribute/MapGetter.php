<?php

namespace Luimedi\Remap\Attribute;

use Attribute;
use Luimedi\Remap\ContextInterface;
use Luimedi\Remap\MappingTarget;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class MapGetter implements MapInterface
{
    public function __construct(protected string $source)
    {
        //
    }

    public function map(mixed $from, ContextInterface $context, MappingTarget $target): mixed
    {
        return $from->{$this->source}();
    }
}
