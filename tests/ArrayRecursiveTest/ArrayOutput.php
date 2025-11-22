<?php

namespace Tests\ArrayRecursiveTest;

use Luimedi\Remap\Attribute\PropertyMapper;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\Cast\CastIterable;
use Luimedi\Remap\Attribute\Cast\CastTransformer;

#[PropertyMapper]
class ArrayOutput
{
    #[MapProperty(source: 'name')]
    public string $name;

    #[MapProperty(source: 'children')]
    #[CastIterable(class: CastTransformer::class)]
    /** @var ArrayOutput[] */
    public array $children = [];

    #[MapProperty(source: 'parent')]
    #[CastTransformer]
    public ?ArrayOutput $parent = null;
}
