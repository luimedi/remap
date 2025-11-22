<?php

namespace Tests\DataObjectTest;

class User
{
    public $address;

    public function __construct(Inner $a)
    {
        $this->address = $a;
    }
}
