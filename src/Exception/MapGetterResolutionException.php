<?php

namespace Luimedi\Remap\Exception;

class MapGetterResolutionException extends RemapException
{
    public static function forTarget(string $targetName): self
    {
        return new self("MapGetter could not resolve a method for target '{$targetName}'");
    }
}
