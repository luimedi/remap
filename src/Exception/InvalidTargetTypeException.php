<?php

namespace Luimedi\Remap\Exception;

class InvalidTargetTypeException extends RemapException
{
    public static function forType(string $type, ?\Throwable $previous = null): self
    {
        return new self("Cannot instantiate mapping target type '{$type}'", 0, $previous);
    }
}
