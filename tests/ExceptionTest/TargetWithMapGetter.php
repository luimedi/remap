<?php

namespace Tests\ExceptionTest;

use Luimedi\Remap\Attribute\MapGetter;
use Luimedi\Remap\Attribute\PropertyMapper;

#[PropertyMapper]
class TargetWithMapGetter
{
    #[MapGetter(source: 'missingMethod')]
    public string $missingMethod;
}
