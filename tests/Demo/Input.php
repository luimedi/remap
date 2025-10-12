<?php

namespace Tests\Demo;

use DateTimeInterface;

class Input
{
    public function __construct(
        public string $name,
        public DateTimeInterface $birthdate,
    ) {}
}
