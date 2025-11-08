<?php

namespace Tests\Demo;

use Luimedi\Remap\Attribute\Cast\CastDateTime;
use Luimedi\Remap\Attribute\Cast\CastTransformer;
use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapGetter;
use Luimedi\Remap\Attribute\MapProperty;

#[ConstructorMapper]
class Output
{
    public function __construct(
        #[MapProperty(source: 'name')]
        public string $name,
        #[MapProperty(source: 'birthdate')]
        #[CastDateTime]
        public string $birthdate,
        #[MapGetter(source: 'getType')]
        public string $type,
        #[MapProperty(source: 'nested')]
        #[CastTransformer]
        public NestedOutput $nested
    ) {}
}
