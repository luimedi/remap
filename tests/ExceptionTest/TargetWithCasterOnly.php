<?php

namespace Tests\ExceptionTest;

use Luimedi\Remap\Attribute\Cast\CastDefault;
use Luimedi\Remap\Attribute\ConstructorMapper;

#[ConstructorMapper]
class TargetWithCasterOnly
{
    public function __construct(
        #[CastDefault(default: 'fallback')]
        public string $value
    ) {}
}
