<?php

namespace Luimedi\Remap\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapProperty
{
    public function __construct(public string $source)
    {
        //
    }
}