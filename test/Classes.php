<?php

namespace Test\Aidphp\Di;

use Psr\Container\ContainerInterface;

class TestWithoutConstructor
{
}

class TestWithConstructor
{
    public function __construct()
    {
    }
}

class TestWithPrivateConstructor
{
    private function __construct()
    {
    }
}

abstract class TestAbstract
{
}

trait TestTrait
{
}

class TestDependency
{
}

class TestNeedDependency
{
    public function __construct(TestDependency $test)
    {
        $this->test = $test;
    }
}

interface TestInterface
{
}

class TestImplementation implements TestInterface
{
}

class TestAnotherImplementation implements TestInterface
{
}

class TestRequireInterface
{
    public function __construct(TestInterface $test)
    {
        $this->test = $test;
    }
}

class TestWithNullValue
{
    public function __construct($arg = null)
    {
        $this->arg = $arg;
    }
}

class TestWithDefaultValue
{
    public function __construct($arg = 'foo')
    {
        $this->arg = $arg;
    }
}

class TestWithArguments
{
    public function __construct($arg1, $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}

class TestOptionalInterface
{
    public function __construct(TestInterface $test = null)
    {
        $this->test = $test;
    }
}

interface TestFactoryInterface
{
    function __invoke(ContainerInterface $dic);
}