<?php

namespace Tests\RecursivePropertyTest;

use Luimedi\Remap\Attribute\PropertyMapper;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\Cast\CastTransformer;

#[PropertyMapper]
class RecursiveOutputProp
{
    #[MapProperty(source: 'name')]
    public string $name;

    #[MapProperty(source: 'parent')]
    #[CastTransformer]
    public ?RecursiveOutputProp $parent = null;
}
