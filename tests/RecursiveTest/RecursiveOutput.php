<?php

namespace Tests\RecursiveTest;

use Luimedi\Remap\Attribute\Cast\CastTransformer;
use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;

#[ConstructorMapper]
class RecursiveOutput
{
    public function __construct(
        #[MapProperty(source: 'name')]
        public string $name,

        #[MapProperty(source: 'parent')]
        #[CastTransformer]
        public ?RecursiveOutput $parent = null,
    ) {
    }
}
