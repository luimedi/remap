<?php

namespace Luimedi\Remap\Exception;

use Throwable;

class MappingExecutionException extends RemapException
{
    /**
     * @param array<int, array<string, mixed>> $mappingTrace
     */
    public static function fromThrowable(Throwable $previous, array $mappingTrace = []): self
    {
        $message = sprintf(
            'Mapping execution failed: %s',
            $previous->getMessage() !== '' ? $previous->getMessage() : $previous::class
        );

        return new self($message, 0, $previous, $mappingTrace);
    }
}
