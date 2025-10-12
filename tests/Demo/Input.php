<?php

namespace Tests\Demo;

class Input
{
    public function __construct(
        public string $name,
        public int $age = 30
    ) {}
}
