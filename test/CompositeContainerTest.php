<?php

declare(strict_types=1);

namespace Test\Aidphp\Di;

use PHPUnit\Framework\TestCase;
use Aidphp\Di\CompositeContainer;
use Psr\Container\ContainerInterface;
use Aidphp\Di\NotFoundException;
use Aidphp\Di\Container;

class CompositeContainerTest extends TestCase
{
    public function testConstructor()
    {
        $container = new CompositeContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testGet()
    {
        $container = new CompositeContainer();
        $container->push(new Container(['id' => 'foo']));

        $this->assertSame('foo', $container->get('id'));
    }

    public function testHas()
    {
        $container = new CompositeContainer();
        $container->push(new Container(['id' => 'foo']));

        $this->assertTrue($container->has('id'));
        $this->assertFalse($container->has('foo'));
    }

    public function testGetNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Identifier "id" is not defined');

        $container = new CompositeContainer();
        $container->get('id');
    }

    public function testGetHasWithScope()
    {
        $container = new CompositeContainer();
        $this->assertFalse($container->has('id'));

        $dic1 = new Container(['id' => 'foo']);
        $container->push($dic1);

        $this->assertTrue($container->has('id'));
        $this->assertSame('foo', $container->get('id'));

        $dic2 = new Container(['id' => 'bar']);
        $container->push($dic2);

        $this->assertTrue($container->has('id'));
        $this->assertSame('bar', $container->get('id'));

        $last = $container->pop();
        $this->assertSame($last, $dic2);

        $this->assertTrue($container->has('id'));
        $this->assertSame('foo', $container->get('id'));

        $last = $container->pop();
        $this->assertSame($last, $dic1);

        $this->assertFalse($container->has('id'));
    }
}