<?php

namespace Luimedi\Remap\Attribute;

use Attribute;
use Luimedi\Remap\ContextInterface;
use Luimedi\Remap\Data;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class MapProperty implements MapInterface
{
    public function __construct(protected string $source)
    {
        //
    }

    public function map(mixed $from, ContextInterface $context): mixed
    {
        return Data::get($from, $this->source);
    }
}
