<?php

namespace Luimedi\Remap\Attribute\Cast;

use Luimedi\Remap\Contracts\CastInterface;
use Luimedi\Remap\Contracts\ContextInterface;
use Luimedi\Remap\Contracts\MappingTargetInterface;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
class CastDefault implements CastInterface
{
    /**
     * @param mixed $default the default value to return if the input is null or empty
     * @param bool  $strict  If strict is true, only null values will be replaced by the default value.
     *                       Otherwise if strict is false, empty values (null, '', 0, false) will be replaced by the default value.
     */
    public function __construct(protected mixed $default = null, protected bool $strict = false)
    {
    }

    public function cast(mixed $value, ContextInterface $context, MappingTargetInterface $mappingTarget): mixed
    {
        if ($this->strict) {
            if (is_null($value)) {
                return $this->default;
            }

            return $value;
        }

        if (empty($value)) {
            return $this->default;
        }

        return $value;
    }
}
