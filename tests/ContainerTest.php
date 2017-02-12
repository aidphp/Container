<?php

namespace Aidphp\Container;

use PHPUnit\Framework\TestCase;
use Aidphp\Container\Assets\Service;

class ContainerTest extends TestCase
{
    public function testRegisterCallable()
    {
        $container = new Container();
        $this->assertSame($container, $container->register('service', function () {
            return new Service();
        }));
    }

    public function testSetService()
    {
        $container = new Container();
        $service = new Service();

        $container->set('service', $service);
        $this->assertSame($service, $container->get('service'));
    }

    public function testGetService()
    {
        $container = new Container();
        $container->register('service', function () {
            return new Service();
        });

        $this->assertInstanceOf('Aidphp\Container\Assets\Service', $container->get('service'));
    }

    public function testHasService()
    {
        $container = new Container();
        $container->register('service', function () {
            return new Service();
        });

        $this->assertTrue($container->has('service'));
        $this->assertFalse($container->has('non_existent'));
    }

    /**
     * @expectedException \Aidphp\Container\ServiceNotFoundException
     * @expectedExceptionMessage The service "foo" does not exist.
     */
    public function testServiceDoesNotExist()
    {
        $container = new Container();
        echo $container->get('foo');
    }
}