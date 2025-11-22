<?php

namespace Tests\CastDefaultTest;

use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\Cast\CastDefault;
use Luimedi\Remap\Attribute\MapProperty;

#[ConstructorMapper]
class OutputNonStrict
{
    public function __construct(
        #[MapProperty(source: 'maybe')]
        #[CastDefault(default: 'fallback', strict: false)]
        public string $maybe
    ) {}
}
