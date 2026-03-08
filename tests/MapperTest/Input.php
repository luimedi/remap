<?php

namespace Tests\MapperTest;

class Input
{
    public $nested = [
        'body' => 'example body',
    ];

    public function __construct(
        public string $name,
        public \DateTimeInterface $birthdate,
    ) {
    }

    public function type(): string
    {
        return 'demo';
    }
}
