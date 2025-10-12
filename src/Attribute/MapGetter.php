<?php

namespace Luimedi\Remap\Attribute;

use Attribute;
use Luimedi\Remap\Cast\CastInterface;
use Luimedi\Remap\Context;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapGetter implements MapInterface
{
    public function __construct(protected string $source)
    {
        //
    }

    public function map(mixed $from, Context $context): mixed
    {
        return $from->{$this->source}();
    }
}
