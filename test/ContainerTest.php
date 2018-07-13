<?php

declare(strict_types=1);

namespace Test\Aidphp\Di;

use PHPUnit\Framework\TestCase;
use Aidphp\Di\Container;
use Psr\Container\ContainerInterface;
use Aidphp\Di\RootContainerAwareInterface;
use Aidphp\Di\ContainerException;
use Aidphp\Di\NotFoundException;
use stdClass;

class ContainerTest extends TestCase
{
    public function testConstructor()
    {
        $container = new Container();
        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertInstanceOf(RootContainerAwareInterface::class, $container);

        $this->assertTrue($container->has(ContainerInterface::class));
        $this->assertTrue($container->has(Container::class));
        $this->assertSame($container, $container->get(ContainerInterface::class));
        $this->assertSame($container, $container->get(Container::class));
    }

    public function testConstructorWithParams()
    {
        $service = new stdClass();
        $container = new Container(['id' => 'foo', stdClass::class => $service]);
        $this->assertTrue($container->has('id'));
        $this->assertSame('foo', $container->get('id'));
        $this->assertTrue($container->has(stdClass::class));
        $this->assertSame($service, $container->get(stdClass::class));
    }

    public function testConstructorWithRoot()
    {
        $root = new Container();
        $container = new Container([], $root);

        $this->assertTrue($container->has(ContainerInterface::class));
        $this->assertTrue($container->has(Container::class));
        $this->assertSame($root, $container->get(ContainerInterface::class));
        $this->assertSame($root, $container->get(Container::class));
    }

    public function testSetRemoveRoot()
    {
        $root = new Container();
        $container = new Container();
        $this->assertSame($container, $container->setRoot($root));

        $this->assertTrue($container->has(ContainerInterface::class));
        $this->assertTrue($container->has(Container::class));
        $this->assertSame($root, $container->get(ContainerInterface::class));
        $this->assertSame($root, $container->get(Container::class));

        $this->assertSame($container, $container->setRoot());
        $this->assertTrue($container->has(ContainerInterface::class));
        $this->assertTrue($container->has(Container::class));
        $this->assertSame($container, $container->get(ContainerInterface::class));
        $this->assertSame($container, $container->get(Container::class));
    }

    public function testRegisterGetHas()
    {
        $container = new Container();
        $this->assertSame($container, $container->register('id', function ($dic) {return new stdClass();}));
        $this->assertTrue($container->has('id'));
        $this->assertInstanceOf('stdClass', $container->get('id'));
    }

    /**
     * @dataProvider getValues
     */
    public function testSetGetHas($id, $value)
    {
        $container = new Container();
        $this->assertSame($container, $container->set($id, $value));
        $this->assertTrue($container->has($id));
        $this->assertSame($value, $container->get($id));
    }

    public function getValues()
    {
        return [
            'null'    => ['id', null],
            'empty'   => ['id', ''],
            'string'  => ['id', 'string'],
            'int'     => ['id', 5],
            'array'   => ['id', ['foo', 'bar']],
            'closure' => ['id', function () {return;}],
            'object'  => ['id', new stdClass()],
        ];
    }

    /**
     * @dataProvider getReplaceValues
     */
    public function testReplace(array $data, $id, $value)
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Identifier "' . $id . '" is already defined, cannot replace it');

        $container = new Container($data);
        $container->set($id, $value);
    }

    public function getReplaceValues()
    {
        return [
            [['id' => 'foo'], 'id', 'bar'],
            [[], ContainerInterface::class, 'foo'],
            [[], Container::class, 'foo'],
        ];
    }

    public function testGetNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Identifier "id" is not defined');

        $container = new Container();
        $container->get('id');
    }

    public function testGetNewInstance()
    {
        $container = new Container();
        $this->assertSame($container, $container->register('id', function ($dic) {return new stdClass();}, false));
        $this->assertNotSame($container->get('id'), $container->get('id'));
    }

    public function testGetShareInstance()
    {
        $container = new Container();
        $this->assertSame($container, $container->register('id', function ($dic) {return new stdClass();}));
        $this->assertSame($container->get('id'), $container->get('id'));
    }

    public function testContainerFactoryParameter()
    {
        $container = new Container();
        $this->assertSame($container, $container->register('id', function ($dic) {return $dic;}));
        $this->assertInstanceOf(Container::class, $container->get('id'));
        $this->assertSame($container, $container->get('id'));
    }

    public function testChangeContainerFactoryParameterForNewInstance()
    {
        $container = new Container();
        $this->assertSame($container, $container->register('id', function ($dic) {return $dic;}, false));
        $this->assertSame($container, $container->get('id'));

        $root = new Container();
        $this->assertSame($container, $container->setRoot($root));
        $this->assertSame($root, $container->get('id'));

        $this->assertSame($container, $container->setRoot());
        $this->assertSame($container, $container->get('id'));
    }

    public function testSameContainerFactoryParameterForSameInstance()
    {
        $container = new Container();
        $this->assertSame($container, $container->register('id', function ($dic) {return $dic;}));
        $this->assertSame($container, $container->get('id'));

        $root = new Container();
        $this->assertSame($container, $container->setRoot($root));
        $this->assertSame($container, $container->get('id'));

        $this->assertSame($container, $container->setRoot());
        $this->assertSame($container, $container->get('id'));
    }
}