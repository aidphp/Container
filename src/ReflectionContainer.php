<?php

declare(strict_types=1);

namespace Aidphp\Di;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionException;
use TypeError;

class ReflectionContainer implements ContainerInterface, RootContainerAwareInterface
{
    protected $root;

    public function __construct(ContainerInterface $container = null)
    {
        $this->setRoot($container);
    }

    public function setRoot(ContainerInterface $container = null): self
    {
        $this->root = $container ?? $this;
        return $this;
    }

    public function get($id)
    {
        try
        {
            $refClass = new ReflectionClass($id);
        }
        catch (ReflectionException $e)
        {
            throw new NotFoundException($id);
        }

        $refMethod = $refClass->getConstructor();

        try
        {
            return $refMethod ? $refClass->newInstanceArgs($this->resolveArguments($refMethod)) : new $id;
        }
        catch (TypeError $e)
        {
            throw new ContainerException($e->getMessage());
        }
    }

    public function has($id): bool
    {
        return class_exists($id);
    }

    protected function resolveArguments(ReflectionFunctionAbstract $method)
    {
        $arguments = [];

        foreach ($method->getParameters() as $parameter)
        {
            $arguments[] = $this->resolveArgument($parameter);
        }

        return $arguments;
    }

    protected function resolveArgument(ReflectionParameter $parameter)
    {
        $class = $parameter->getClass();

        if ($class)
        {
            $resolved = $this->root->get($class->name);
        }
        elseif ($parameter->isDefaultValueAvailable())
        {
            $resolved = $parameter->getDefaultValue();
        }
        else
        {
            $resolved = null;
        }

        return $resolved;
    }
}