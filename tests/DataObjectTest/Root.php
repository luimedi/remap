<?php

namespace Tests\DataObjectTest;

class Root
{
    public $user;

    public function __construct(User $u)
    {
        $this->user = $u;
    }
}
