<?php

namespace Luimedi\Remap\Attribute\Cast;

use Attribute;
use Luimedi\Remap\ContextInterface;
use Luimedi\Remap\Attribute\Cast\CastInterface;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class CastTransformer implements CastInterface
{
    public function cast(mixed $value, ContextInterface $context): mixed
    {
        /** @var \Luimedi\Remap\EngineInterface $engine */
        $engine = $context->get('__engine__');
        // If the value is null, nothing to map.
        if ($value === null) {
            return null;
        }
        // Leave scalar (non-array, non-object) values untouched — CastTransformer
        // should transform arrays (they may resolve to a class via binding)
        // and objects, but simple scalars (string/int/float/bool) should be
        // returned as-is so mixed arrays work.
        if (!is_object($value) && !is_array($value)) {
            return $value;
        }

        // If we already mapped this source object, return the mapped instance.
        if (is_object($value)) {
            $id = spl_object_hash($value);
            $registry = $context->get('__mapping_registry__', []);
            if (isset($registry[$id])) {
                return $registry[$id];
            }

            // Guard against infinite recursion when casting objects that reference their parent.
            // We keep a simple stack of source object ids in the context under '__casting_stack__'.
            $stack = $context->get('__casting_stack__', []);

            // If we're already casting this source, there is a recursion. If a registry entry
            // exists return it, otherwise return null to satisfy typed constructors.
            if (in_array($id, $stack, true)) {
                return $registry[$id] ?? null;
            }

            // Mark this source as being cast and ensure we clean up afterwards.
            $stack[] = $id;
            $context->set('__casting_stack__', $stack);

            try {
                $type = $engine->resolve($value, $context);
                $result = $engine->execute($value, $type, $context);
            } finally {
                array_pop($stack);
                $context->set('__casting_stack__', $stack);
            }

            return $result;
        }

        $type = $engine->resolve($value, $context);

        return $engine->execute($value, $type, $context);
    }
}
