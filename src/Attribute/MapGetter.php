<?php

namespace Luimedi\Remap\Attribute;

use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MapInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;
use Luimedi\Remap\Exception\MapGetterResolutionException;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
class MapGetter implements MapInterface
{
    public function __construct(protected ?string $source = null)
    {
    }

    public function map(mixed $from, ContextInterface $context, MappingTargetInterface $target): mixed
    {
        $method = $this->source;

        if (null === $method) {
            $getterMethod = 'get'.ucfirst($target->getName());

            if (is_object($from) && method_exists($from, $getterMethod)) {
                $method = $getterMethod;
            } elseif (is_object($from) && method_exists($from, $target->getName())) {
                $method = $target->getName();
            }
        }

        if (!is_object($from) || !is_string($method) || !method_exists($from, $method)) {
            throw MapGetterResolutionException::forTarget($target->getName());
        }

        return $from->{$method}();
    }
}
