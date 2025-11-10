<?php

namespace Luimedi\Remap\Attribute\Cast;

use ArrayIterator;
use Attribute;
use Luimedi\Remap\ContextInterface;

#[Attribute(Attribute::TARGET_PARAMETER)]
class CastIterable implements CastInterface
{
    public function __construct(private string $class, private array $args = [])
    {
    }

    public function cast(mixed $value, ContextInterface $context): mixed
    {
        $caster = new $this->class(...$this->args);
        $output = [];

        foreach ($value as $item) {
            $output[] = $caster->cast($item, $context);
        }

        return $output;
    }
}