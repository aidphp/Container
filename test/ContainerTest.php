<?php

namespace Test\Aidphp\Di;

use PHPUnit\Framework\TestCase;
use Aidphp\Di\Container;
use stdClass;

class ContainerTest extends TestCase
{
    protected $dic;

    public function setUp()
    {
        $this->dic = new Container();
    }

    public function testClassWithoutConstructor()
    {
        $this->assertInstanceOf(TestWithoutConstructor::class, $this->dic->get(TestWithoutConstructor::class));
    }

    public function testClassWithConstructor()
    {
        $this->assertInstanceOf(TestWithConstructor::class, $this->dic->get(TestWithConstructor::class));
    }

    /**
     * @expectedException Aidphp\Di\NotFoundException
     * @expectedExceptionMessage Unable to resolve "UnknowClass"
     */
    public function testWithClassNotFount()
    {
        $this->dic->get('UnknowClass');
    }

    /**
     * @dataProvider getInvalidClass
     * @expectedException Aidphp\Di\ContainerException
     */
    public function testWithInvalidClass($class)
    {
        $this->dic->get($class);
    }

    public function getInvalidClass()
    {
        return [
            [TestInterface::class],
            [TestWithPrivateConstructor::class],
            [TestAbstract::class],
            [TestTrait::class]
        ];
    }

    public function testClassWithConcreteTypehint()
    {
        $instance = $this->dic->get(TestNeedDependency::class);

        $this->assertInstanceOf(TestNeedDependency::class, $instance);
        $this->assertInstanceOf(TestDependency::class, $instance->test);
    }

    public function testClassImplementInterface()
    {
        $this->dic->define(TestInterface::class, TestImplementation::class);

        $instance = $this->dic->get(TestInterface::class);

        $this->assertInstanceOf(TestInterface::class, $instance);
        $this->assertInstanceOf(TestImplementation::class, $instance);
    }

    public function testClassWithInterfaceDependency()
    {
        $this->dic->define(TestInterface::class, TestImplementation::class);

        $instance = $this->dic->get(TestRequireInterface::class);

        $this->assertInstanceOf(TestRequireInterface::class, $instance);
        $this->assertInstanceOf(TestInterface::class, $instance->test);
        $this->assertInstanceOf(TestImplementation::class, $instance->test);
    }

    /**
     * @expectedException Aidphp\Di\ParameterNotFoundException
     * @expectedExceptionMessage Unable to resolve the parameter 1 named $test of type Test\Aidphp\Di\TestInterface in Test\Aidphp\Di\TestRequireInterface::__construct()
     */
    public function testClassWithoutImplementation()
    {
        $this->dic->get(TestRequireInterface::class);
    }

    public function testClassWithNullValue()
    {
        $instance = $this->dic->get(TestWithNullValue::class);

        $this->assertInstanceOf(TestWithNullValue::class, $instance);
        $this->assertNull($instance->arg);
    }

    public function testClassWithDefaultValue()
    {
        $instance = $this->dic->get(TestWithDefaultValue::class);

        $this->assertInstanceOf(TestWithDefaultValue::class, $instance);
        $this->assertSame('foo', $instance->arg);
    }

    public function testClassWithArguments()
    {
        $this->dic->define(TestWithArguments::class, null, ['foo', 'bar']);

        $instance = $this->dic->get(TestWithArguments::class);

        $this->assertSame('foo', $instance->arg1);
        $this->assertSame('bar', $instance->arg2);
    }

    /**
     * @expectedException Aidphp\Di\ParameterNotFoundException
     * @expectedExceptionMessage Unable to resolve the parameter 1 named $arg1 in Test\Aidphp\Di\TestWithArguments::__construct()
     */
    public function testClassWithNullArguments()
    {
        $this->dic->get(TestWithArguments::class);
    }

    public function testShareInstance()
    {
        $this->dic->define(TestNeedDependency::class);

        $instance1 = $this->dic->get(TestNeedDependency::class);
        $instance2 = $this->dic->get(TestNeedDependency::class);

        $this->assertSame($instance1, $instance2);
        $this->assertSame($instance1->test, $instance2->test);
    }

    public function testDelegate()
    {
        $factory = $this->createMock(TestFactoryInterface::class);

        $factory->expects($this->once())
            ->method('__invoke')
            ->with($this->dic)
            ->will($this->returnValue(new TestDependency()));

        $this->dic->delegate(TestDependency::class, $factory);

        $this->assertInstanceOf(TestDependency::class, $this->dic->get(TestDependency::class));
    }

    public function testClassWithOptionalInterface()
    {
        $instance = $this->dic->get(TestOptionalInterface::class);

        $this->assertInstanceOf(TestOptionalInterface::class, $instance);
        $this->assertNull($instance->test);
    }

    public function testClassWithOptionalInterfaceResolveByAlias()
    {
        $this->dic->define(TestInterface::class, TestImplementation::class);
        $instance = $this->dic->get(TestOptionalInterface::class);

        $this->assertInstanceOf(TestOptionalInterface::class, $instance);
        $this->assertInstanceOf(TestImplementation::class, $instance->test);
    }

    public function testClassWithOptionalInterfaceResolveByDelegate()
    {
        $this->dic->delegate(TestInterface::class, function ($dic) {return new TestImplementation();});
        $instance = $this->dic->get(TestOptionalInterface::class);

        $this->assertInstanceOf(TestOptionalInterface::class, $instance);
        $this->assertInstanceOf(TestImplementation::class, $instance->test);
    }

    /**
     * @dataProvider getClasses
     */
    public function testHasClass(string $id, bool $flag)
    {
        $this->assertSame($flag, $this->dic->has($id));
    }

    public function getClasses()
    {
        return [
            [TestWithoutConstructor::class, true],
            [TestWithConstructor::class, true],
            [TestNeedDependency::class, true],
            [TestDependency::class, true],
            [TestImplementation::class, true],
            [TestRequireInterface::class, true],
            [TestWithNullValue::class, true],
            [TestWithDefaultValue::class, true],
            [TestWithArguments::class, true],
            [TestOptionalInterface::class, true],
            [TestWithPrivateConstructor::class, true],
            [stdClass::class, true],

            ['UnknowClass', false],
            [TestInterface::class, false],
            [TestFactoryInterface::class, false],
        ];
    }

    public function testUpdateDefinition()
    {
        $this->dic->define(TestInterface::class, TestImplementation::class, [], false);
        $instance1 = $this->dic->get(TestRequireInterface::class);

        $this->dic->define(TestInterface::class, TestAnotherImplementation::class, [], false);
        $instance2 = $this->dic->get(TestRequireInterface::class);

        $this->assertNotSame($instance1, $instance2);
        $this->assertNotSame($instance1->test, $instance2->test);
        $this->assertInstanceOf(TestImplementation::class, $instance1->test);
        $this->assertInstanceOf(TestAnotherImplementation::class, $instance2->test);
    }

    public function testCloneShareDefinitionsAndInstances()
    {
        $this->dic->define(TestNeedDependency::class);
        $instance1 = $this->dic->get(TestNeedDependency::class);

        $dic2 = clone $this->dic;
        $instance2 = $dic2->get(TestNeedDependency::class);

        $this->assertSame($instance1, $instance2);
        $this->assertSame($instance1->test, $instance2->test);
    }

    public function testCloneDoNotShareNewInstance()
    {
        $instance1 = $this->dic->get(TestNeedDependency::class);

        $dic2 = clone $this->dic;
        $instance2 = $dic2->get(TestNeedDependency::class);

        $this->assertNotSame($instance1, $instance2);
    }

    public function testCloneAndUpdateDefinition()
    {
        $this->dic->define(TestInterface::class, TestImplementation::class, [], false);
        $instance1 = $this->dic->get(TestRequireInterface::class);

        $dic2 = clone $this->dic;
        $dic2->define(TestInterface::class, TestAnotherImplementation::class, [], false);
        $instance2 = $dic2->get(TestRequireInterface::class);

        $this->assertNotSame($instance1, $instance2);
        $this->assertNotSame($instance1->test, $instance2->test);
        $this->assertInstanceOf(TestImplementation::class, $instance1->test);
        $this->assertInstanceOf(TestAnotherImplementation::class, $instance2->test);
    }
}