<?php

namespace Tests\MapperTest;

use DateTimeInterface;

class Input
{
    public $nested = [
        'body' => 'example body'
    ];
    
    public function __construct(
        public string $name,
        public DateTimeInterface $birthdate,
    ) {}

    public function getType(): string
    {
        return 'demo';
    }
}
