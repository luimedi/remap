<?php

namespace Luimedi\Remap\Exception;

class MissingMappedValueException extends RemapException
{
    public static function forParameter(string $parameterName): self
    {
        return new self("Cannot cast parameter '{$parameterName}' because it has no value.");
    }
}
