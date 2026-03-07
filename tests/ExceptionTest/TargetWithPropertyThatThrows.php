<?php

namespace Tests\ExceptionTest;

use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;

#[ConstructorMapper]
class TargetWithPropertyThatThrows
{
    public function __construct(
        #[MapProperty(source: 'data')]
        #[ThrowingCaster]
        public string $data
    ) {}
}
