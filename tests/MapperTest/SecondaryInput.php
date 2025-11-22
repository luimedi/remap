<?php

namespace Tests\MapperTest;

use DateTimeInterface;

class SecondaryInput
{
    /** @var DateTimeInterface[] */
    public function __construct(
        public array $dates,
    ) {}
}
