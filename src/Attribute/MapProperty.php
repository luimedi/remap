<?php

namespace Luimedi\Remap\Attribute;

use Attribute;
use Luimedi\Remap\ContextInterface;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapProperty implements MapInterface
{
    public function __construct(protected string $source)
    {
        //
    }

    public function map(mixed $from, ContextInterface $context): mixed
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
