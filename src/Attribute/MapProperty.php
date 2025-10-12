<?php

namespace Luimedi\Remap\Attribute;

use Attribute;
use Luimedi\Remap\Cast\CastInterface;
use Luimedi\Remap\Context;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapProperty implements MapInterface
{
    public function __construct(protected string $source)
    {
        //
    }

    public function map(mixed $from, Context $context): mixed
    {
        $output = null;
        
        if (is_array($from) && array_key_exists($this->source, $from)) {
            $output = $from[$this->source];
        }

        if (is_object($from) && property_exists($from, $this->source)) {
            $output = $from->{$this->source};
        }

        return $output;
    }
}
