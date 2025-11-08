<?php

namespace Luimedi\Remap\Attribute;

use Attribute;
use Luimedi\Remap\ContextInterface;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapGetter implements MapInterface
{
    public function __construct(protected string $source)
    {
        //
    }

    public function map(mixed $from, ContextInterface $context): mixed
    {
        return $from->{$this->source}();
    }
}
