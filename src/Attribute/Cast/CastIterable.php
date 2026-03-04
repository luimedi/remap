<?php

namespace Luimedi\Remap\Attribute\Cast;

use ArrayIterator;
use Attribute;
use Luimedi\Remap\Contracts\CastInterface;
use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class CastIterable implements CastInterface
{
    public function __construct(private string $class, private array $args = [])
    {
    }

    public function cast(mixed $value, ContextInterface $context, MappingTargetInterface $mappingTarget): mixed
    {
        $caster = new $this->class(...$this->args);
        $output = [];

        foreach ($value as $item) {
            $output[] = $caster->cast($item, $context, $mappingTarget);
        }

        return $output;
    }
}