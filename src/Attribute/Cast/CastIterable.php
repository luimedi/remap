<?php

namespace Luimedi\Remap\Attribute\Cast;

use Luimedi\Remap\Contracts\CastInterface;
use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
class CastIterable implements CastInterface
{
    public function __construct(private string $class, private array $args = [])
    {
    }

    public function cast(mixed $value, ContextInterface $context, MappingTargetInterface $mappingTarget): mixed
    {
        if (!class_exists($this->class)) {
            throw new \InvalidArgumentException(sprintf('Caster class "%s" does not exist.', $this->class));
        }

        $caster = new $this->class(...$this->args);

        if (!$caster instanceof CastInterface) {
            throw new \InvalidArgumentException(sprintf('Caster class "%s" must implement %s.', $this->class, CastInterface::class));
        }

        if (!is_iterable($value)) {
            throw new \InvalidArgumentException('Value must be iterable to be cast as iterable.');
        }

        $output = [];

        foreach ($value as $item) {
            $output[] = $caster->cast($item, $context, $mappingTarget);
        }

        return $output;
    }
}
