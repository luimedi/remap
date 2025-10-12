<?php

namespace Tests\Demo;

use Luimedi\Remap\Attribute\MapProperty;

class Output
{
    public function __construct(
        #[MapProperty(source: 'name')]
        public string $name,
        #[MapProperty(source: 'age')]
        public int $age = 30
    ) {}
}