<?php

namespace Luimedi\Remap\Attribute\Cast;

use Attribute;
use DateTime;
use DateTimeInterface;
use Luimedi\Remap\Contracts\CastInterface;
use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class CastDateTime implements CastInterface
{
    public function __construct(protected ?string $format = DateTime::ATOM)
    {
    }

    public function cast(mixed $value, ContextInterface $context, MappingTargetInterface $mappingTarget): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTime::ATOM);
        }

        if (is_string($value)) {
            $date = new DateTime($value);
            return $date->format(DateTime::ATOM);
        }

        return null;
    }
}