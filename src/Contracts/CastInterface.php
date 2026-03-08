<?php

namespace Luimedi\Remap\Contracts;

interface CastInterface
{
    public function cast(mixed $value, ContextInterface $context, MappingTargetInterface $mappingTarget): mixed;
}
