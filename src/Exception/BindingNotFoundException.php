<?php

namespace Luimedi\Remap\Exception;

class BindingNotFoundException extends RemapException
{
    public static function forType(string $type): self
    {
        return new self("No binding found for {$type}");
    }
}
