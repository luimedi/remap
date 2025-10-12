<?php

namespace Luimedi\Remap\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapGetter
{
    public function __construct(public string $source)
    {
        //
    }
}
