<?php

namespace Luimedi\Remap\Exception;

use RuntimeException;
use Throwable;

abstract class RemapException extends RuntimeException
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $mappingTrace = [];

    /**
     * @param array<int, array<string, mixed>> $mappingTrace
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, array $mappingTrace = [])
    {
        parent::__construct($message, $code, $previous);
        $this->mappingTrace = $mappingTrace;
    }

    /**
     * @param array<int, array<string, mixed>> $trace
     */
    public function appendMappingTrace(array $trace): static
    {
        if ($trace === []) {
            return $this;
        }

        $existing = array_map('serialize', $this->mappingTrace);

        foreach ($trace as $step) {
            $serialized = serialize($step);

            if (!in_array($serialized, $existing, true)) {
                $this->mappingTrace[] = $step;
                $existing[] = $serialized;
            }
        }

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMappingTrace(): array
    {
        return $this->mappingTrace;
    }
}
