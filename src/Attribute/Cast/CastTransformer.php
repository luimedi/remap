<?php

namespace Luimedi\Remap\Attribute\Cast;

use Attribute;
use Luimedi\Remap\ContextInterface;
use Luimedi\Remap\Attribute\Cast\CastInterface;

#[Attribute(Attribute::TARGET_PARAMETER)]
class CastTransformer implements CastInterface
{
    public function cast(mixed $value, ContextInterface $context): mixed
    {
        /** @var \Luimedi\Remap\EngineInterface $engine */
        $engine = $context->get('__engine__');
        $type = $engine->resolve($value, $context);

        return $engine->execute($value, $type, $context);
    }
}
