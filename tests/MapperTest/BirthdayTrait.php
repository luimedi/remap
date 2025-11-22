<?php

namespace Tests\MapperTest;

use Luimedi\Remap\Attribute\Cast\CastDateTime;
use Luimedi\Remap\Attribute\MapProperty;

trait BirthdayTrait
{
    #[MapProperty(source: 'birthdate')]
    #[CastDateTime()]
    public string $birthdate;
}
