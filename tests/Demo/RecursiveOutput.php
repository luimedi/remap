<?php

namespace Tests\Demo;

use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\Cast\CastTransformer;

#[ConstructorMapper]
class RecursiveOutput
{
    public function __construct(
        #[MapProperty(source: 'name')]
        public string $name,

        #[MapProperty(source: 'parent')]
        #[CastTransformer]
        public ?RecursiveOutput $parent = null
    ) {}
}
