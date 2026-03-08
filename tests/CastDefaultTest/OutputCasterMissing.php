<?php

namespace Tests\CastDefaultTest;

use Luimedi\Remap\Attribute\Cast\CastDefault;
use Luimedi\Remap\Attribute\ConstructorMapper;

#[ConstructorMapper]
class OutputCasterMissing
{
    public function __construct(
        #[CastDefault(default: 'fallback')]
        public mixed $maybe,
    ) {
    }
}
