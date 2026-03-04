<?php

namespace Luimedi\Remap;

use Luimedi\Remap\Contracts\MappingTargetInterface;

class MappingTarget implements MappingTargetInterface
{
    public function __construct(
        private string $name,
        private ?string $type = null
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}
