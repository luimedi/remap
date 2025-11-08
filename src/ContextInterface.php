<?php

namespace Luimedi\Remap;

use Countable;
use IteratorAggregate;
use ArrayAccess;

interface ContextInterface extends ArrayAccess, IteratorAggregate, Countable
{
    public function set(string $key, mixed $value): void;

    public function get(string $key, mixed $default = null): mixed;

    public function all(): array;
}