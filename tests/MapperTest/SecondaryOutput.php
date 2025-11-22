<?php

namespace Tests\MapperTest;

use Luimedi\Remap\Attribute\Cast\CastDateTime;
use Luimedi\Remap\Attribute\Cast\CastIterable;
use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;

#[ConstructorMapper]
class SecondaryOutput
{
    /** @var string[] */
    public function __construct(
        #[MapProperty(source: 'dates')]
        #[CastIterable(class: CastDateTime::class)]
        public array $dates,
    ) {}
}
