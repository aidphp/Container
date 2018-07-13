<?php

namespace Test\Aidphp\Di\Asset;

class Qux
{
    public $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }
}