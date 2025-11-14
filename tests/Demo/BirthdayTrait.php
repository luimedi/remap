<?php

namespace Tests\Demo;

use Luimedi\Remap\Attribute\Cast\CastDateTime;
use Luimedi\Remap\Attribute\MapProperty;

trait BirthdayTrait
{
    #[MapProperty(source: 'birthdate')]
    #[CastDateTime()]
    public string $birthdate;
}
