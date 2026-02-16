<?php

namespace Luimedi\Remap;

class MappingTarget
{
    public function __construct(
        public string $name,
        public ?string $type = null
    ) {}
}
