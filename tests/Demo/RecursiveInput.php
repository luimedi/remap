<?php

namespace Tests\Demo;

class RecursiveInput
{
    public ?RecursiveInput $parent = null;

    public function __construct(public string $name, ?RecursiveInput $parent = null)
    {
        $this->parent = $parent;
    }
}
