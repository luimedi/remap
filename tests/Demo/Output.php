<?php

namespace Tests\Demo;

use Luimedi\Remap\Attribute\MapGetter;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Cast\CastDateTime;
use Luimedi\Remap\Cast\CastDateTimeAtom;

class Output
{
    public function __construct(
        #[MapProperty(source: 'name')]
        public string $name,
        #[MapProperty(source: 'birthdate')]
        #[CastDateTime()]
        public string $birthdate,
    ) {}
}
