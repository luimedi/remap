<?php

namespace Luimedi\Remap\Exception;

class BindingResolutionException extends RemapException
{
    public static function forType(string $type, mixed $resolved): self
    {
        $resolvedDescription = is_string($resolved)
            ? "'{$resolved}'"
            : get_debug_type($resolved);

        return new self("Cannot resolve binding for {$type}. Resolved value: {$resolvedDescription}");
    }
}
