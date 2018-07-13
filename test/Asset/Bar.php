<?php

declare(strict_types=1);

namespace Test\Aidphp\Di\Asset;

class Bar
{
    public $baz;

    public function __construct(Baz $baz)
    {
        $this->baz = $baz;
    }
}