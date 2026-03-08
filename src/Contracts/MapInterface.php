<?php

namespace Luimedi\Remap\Contracts;

interface MapInterface
{
    public function map(mixed $from, ContextInterface $context, MappingTargetInterface $target): mixed;
}
