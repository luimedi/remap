<?php

namespace Tests\PropertyMapperTest;

use Luimedi\Remap\Attribute\Cast\CastDateTime;
use Luimedi\Remap\Attribute\MapProperty;
use Luimedi\Remap\Attribute\PropertyMapper;

#[PropertyMapper]
class Output
{
    #[MapProperty(source: 'birthdate')]
    #[CastDateTime()]
    public string $birthdate;
}