<?php

namespace Tests\MapperTest;

use Luimedi\Remap\Attribute\Cast\CastDateTime;
use Luimedi\Remap\Attribute\Cast\CastTransformer;
use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapGetter;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\PropertyMapper;

#[ConstructorMapper]
#[PropertyMapper]
class Output
{
    use BirthdayTrait;

    public function __construct(
        #[MapProperty(source: 'name')]
        public string $name,
        #[MapGetter(source: 'getType')]
        public string $type,
        #[MapProperty(source: 'nested')]
        #[CastTransformer]
        public NestedOutput $nested
    ) {}
}
