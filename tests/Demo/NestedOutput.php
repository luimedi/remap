<?php

namespace Tests\Demo;

use Luimedi\Remap\Attribute\ConstructorMapper;
use Luimedi\Remap\Attribute\MapProperty;

#[ConstructorMapper]
class NestedOutput
{
    public function __construct(
        #[MapProperty(source: 'body')]
        public string $body
    ) {}
}
