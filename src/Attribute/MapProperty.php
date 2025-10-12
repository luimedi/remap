<?php

namespace Luimedi\Remap\Attribute;

use Attribute;
use Luimedi\Remap\Context;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapProperty
{
    public function __construct(public string $source)
    {
        //
    }

    public function map(mixed $from, Context $context): mixed
    {
        if (is_array($from) && array_key_exists($this->source, $from)) {
            return $from[$this->source];
        }

        if (is_object($from) && property_exists($from, $this->source)) {
            return $from->{$this->source};
        }

        return null;
    }
}
