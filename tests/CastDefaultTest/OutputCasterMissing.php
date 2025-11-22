<?php

namespace Tests\CastDefaultTest;

use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\Cast\CastDefault;

#[ConstructorMapper]
class OutputCasterMissing
{
    public function __construct(
        #[CastDefault(default: 'fallback')]
        public mixed $maybe
    ) {}
}
