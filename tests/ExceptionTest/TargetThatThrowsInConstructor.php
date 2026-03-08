<?php

namespace Tests\ExceptionTest;

use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;

#[ConstructorMapper]
class TargetThatThrowsInConstructor
{
    public function __construct(
        #[MapProperty(source: 'data')]
        public string $data,
    ) {
        throw new \RuntimeException('Constructor error');
    }
}
