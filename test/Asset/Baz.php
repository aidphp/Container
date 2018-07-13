<?php

declare(strict_types=1);

namespace Test\Aidphp\Di\Asset;

class Baz
{
    public $value;
    public $default;

    public function __construct($value, $default = 'test')
    {
        $this->value   = $value;
        $this->default = $default;
    }
}