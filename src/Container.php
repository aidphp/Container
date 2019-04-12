<?php

namespace Aidphp\Di;

use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

class Container implements ContainerInterface
{
    protected $definitions = [];
    protected $factories   = [];
    protected $instances   = [];

    public function define(string $id, string $class = null, array $params = [], bool $shared = true): self
    {
        unset($this->instances[$id], $this->factories[$id]);
        $this->definitions[$id] = [$class ?? $id, $params, $shared];
        return $this;
    }

    public function delegate(string $id, callable $factory, bool $shared = true): self
    {
        unset($this->instances[$id], $this->factories[$id]);
        $this->factories[$id] = [$factory, $shared];
        return $this;
    }

    public function get($id)
    {
        if (isset($this->instances[$id]))
        {
            return $this->instances[$id];
        }

        if (! isset($this->factories[$id]))
        {
            $this->factories[$id] = isset($this->definitions[$id])
                ? [$this->createFactory($this->definitions[$id][0], $this->definitions[$id][1]), $this->definitions[$id][2]]
                : [$this->createFactory($id), false];
        }

        $instance = $this->factories[$id][0]($this);

        if ($this->factories[$id][1])
        {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    public function has($id): bool
    {
        return isset($this->instances[$id])
            || isset($this->factories[$id])
            || isset($this->definitions[$id])
            || class_exists($id);
    }

    protected function createFactory(string $class, array $params = []): callable
    {
        try
        {
            $refClass = new ReflectionClass($class);
        }
        catch (ReflectionException $e)
        {
            throw new NotFoundException($class);
        }

        if (! $refClass->isInstantiable())
        {
            throw new ContainerException('The class "' . $refClass->name . '" can not be instantiate');
        }

        if (null !== ($refMethod = $refClass->getConstructor()))
        {
            $resolver = $this->getArgsResolver($refMethod, $params);
            $factory  = function ($dic) use ($class, $resolver) {return new $class(...$resolver($dic));};
        }
        else
        {
            $factory = function ($dic) use ($class) {return new $class;};
        }

        return $factory;
    }

    protected function getArgsResolver(ReflectionMethod $method, array $params = []): callable
    {
        $paramsInfo = [];

        foreach ($method->getParameters() as $param)
        {
            $paramsInfo[] = [
                ($class = $param->getClass()) ? $class->name : null,
                $param
            ];
        }

        return function ($dic) use ($paramsInfo, $params) {
            $values = [];

            foreach ($paramsInfo as [$class, $param])
            {
                if ($class)
                {
                    try
                    {
                        $values[] = $dic->get($class);
                    }
                    catch (ContainerExceptionInterface $e)
                    {
                        if (! $param->allowsNull())
                        {
                            throw new ParameterNotFoundException($param, $e);
                        }

                        $values[] = null;
                    }
                }
                elseif ($params)
                {
                    $values[] = array_shift($params);
                }
                elseif ($param->isOptional())
                {
                    $values[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                }
                else
                {
                    throw new ParameterNotFoundException($param);
                }
            }

            return $values;
        };
    }
}