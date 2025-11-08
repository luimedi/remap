<?php

namespace Luimedi\Remap\Attribute\Cast;

use Attribute;
use DateTime;
use DateTimeInterface;
use Luimedi\Remap\ContextInterface;

#[Attribute(Attribute::TARGET_PARAMETER)]
class CastDefault implements CastInterface
{
    /**
     * @param mixed $default The default value to return if the input is null or empty.
     * @param bool  $strict  If strict is true, only null values will be replaced by the default value. 
     *                       Otherwise if strict is false, empty values (null, '', 0, false) will be replaced by the default value.
     */
    public function __construct(protected mixed $default = null, protected bool $strict = false)
    {
    }

    public function cast(mixed $value, ContextInterface $context): mixed
    {
        if ($this->strict) {
            if (is_null($value) ) {
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
