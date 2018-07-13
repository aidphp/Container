<?php

declare(strict_types=1);

namespace Test\Aidphp\Di;

use PHPUnit\Framework\TestCase;
use Aidphp\Di\ReflectionContainer;
use Psr\Container\ContainerInterface;
use Aidphp\Di\Container;
use Aidphp\Di\RootContainerAwareInterface;
use Aidphp\Di\NotFoundException;
use stdClass;
use Test\Aidphp\Di\Asset\Baz;
use Test\Aidphp\Di\Asset\Foo;
use Test\Aidphp\Di\Asset\Bar;
use Test\Aidphp\Di\Asset\Qux;
use Aidphp\Di\ContainerException;

class ReflectionContainerTest extends TestCase
{
    public function testConstructor()
    {
        $container = new ReflectionContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertInstanceOf(RootContainerAwareInterface::class, $container);
    }

    public function testGetHasWithoutConstructor()
    {
        $container = new ReflectionContainer();
        $this->assertTrue($container->has(stdClass::class));
        $this->assertInstanceOf(stdClass::class, $container->get(stdClass::class));
    }

    public function testGetHasWithConstructor()
    {
        $container = new ReflectionContainer();
        $this->assertTrue($container->has(Baz::class));

        $baz = $container->get(Baz::class);
        $this->assertInstanceOf(Baz::class, $baz);
        $this->assertNull($baz->value);
        $this->assertSame('test', $baz->default);
    }

    public function testGetHasWithDependencies()
    {
        $container = new ReflectionContainer();
        $this->assertTrue($container->has(Baz::class));

        $foo = $container->get(Foo::class);
        $this->assertInstanceOf(Foo::class, $foo);
        $this->assertInstanceOf(Bar::class, $foo->bar);
        $this->assertInstanceOf(Baz::class, $foo->bar->baz);
        $this->assertInstanceOf(Baz::class, $foo->baz);
        $this->assertNotSame($foo->bar->baz, $foo->baz);
    }

    public function testGetNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Identifier "id" is not defined');

        $container = new ReflectionContainer();
        $container->get('id');
    }

    public function testGetWithUnresolvedArgument()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Argument 1 passed to Test\Aidphp\Di\Asset\Qux::__construct() must be of the type string, null given');

        $container = new ReflectionContainer();
        $container->get(Qux::class);
    }

    public function testConstructorWithRoot()
    {
        $baz = new Baz('root');
        $root = new Container();
        $root->set(Baz::class, $baz);
        $container = new ReflectionContainer($root);

        $this->assertTrue($container->has(Bar::class));
        $bar = $container->get(Bar::class);
        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertInstanceOf(Baz::class, $bar->baz);
        $this->assertSame($baz, $bar->baz);
        $this->assertSame('root', $bar->baz->value);
    }

    public function testSetRemoveRoot()
    {
        $container = new ReflectionContainer();
        $bar = $container->get(Bar::class);
        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertInstanceOf(Baz::class, $bar->baz);
        $this->assertNull($bar->baz->value);

        $baz = new Baz('root');
        $root = new Container();
        $root->set(Baz::class, $baz);
        $this->assertSame($container, $container->setRoot($root));
        $this->assertTrue($container->has(Bar::class));
        $bar2 = $container->get(Bar::class);
        $this->assertNotSame($bar, $bar2);
        $this->assertInstanceOf(Bar::class, $bar2);
        $this->assertInstanceOf(Baz::class, $bar2->baz);
        $this->assertSame($baz, $bar2->baz);
        $this->assertSame('root', $bar2->baz->value);

        $this->assertSame($container, $container->setRoot());
        $bar3 = $container->get(Bar::class);
        $this->assertNotSame($bar3, $bar);
        $this->assertNotSame($bar3, $bar2);
        $this->assertInstanceOf(Bar::class, $bar3);
        $this->assertInstanceOf(Baz::class, $bar3->baz);
        $this->assertNotSame($baz, $bar3->baz);
        $this->assertNull($bar3->baz->value);
    }
}