<?php

namespace Tests\MapperTest;

class SecondaryInput
{
    /** @var \DateTimeInterface[] */
    public function __construct(
        public array $dates,
    ) {
    }
}
