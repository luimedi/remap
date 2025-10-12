<?php

namespace Luimedi\Remap;

class Engine
{
    protected array $attributes = [];
    protected array $casters = [];

    public function __construct()
    {
        // 
    }

    public function execute(mixed $from, string $type, Context $context): mixed
    {
        return -1;
    }
}