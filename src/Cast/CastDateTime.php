<?php

namespace Luimedi\Remap\Cast;

use Attribute;
use DateTime;
use DateTimeInterface;

#[Attribute(Attribute::TARGET_PARAMETER)]
class CastDateTime implements CastInterface
{
    public function __construct(protected ?string $format = DateTime::ATOM)
    {
    }

    public function cast(mixed $value): mixed
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