<?php

namespace Luimedi\Remap\Contracts;

interface MappingTargetInterface
{
    public function getName(): string;

    public function getType(): ?string;
}
