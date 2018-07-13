<?php

declare(strict_types=1);

namespace Test\Aidphp\Di\Asset;

class Foo
{
    public $bar;
    public $baz;

    public function __construct(Bar $bar, Baz $baz)
    {
        $this->bar = $bar;
        $this->baz = $baz;
    }
}