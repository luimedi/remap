<?php

namespace Tests\ArrayRecursiveTest;

class ArrayInput
{
    public ?ArrayInput $parent = null;
    public array $children = [];

    public function __construct(
        public string $name, 
        ?ArrayInput $parent = null, 
        array $children = []
    ) {
        $this->parent = $parent;
        $this->children = $children;
    }
}
