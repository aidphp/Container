<?php

namespace Aidphp\Container;

use Interop\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $services = array();
    protected $factories = array();
    protected $shared = array();

    public function register($id, callable $factory, $shared = true)
    {
        $this->factories[$id] = $factory;
        $this->shared[$id] = (bool) $shared;
        return $this;
    }

    public function set($id, $service)
    {
        $this->services[$id] = $service;
        return $this;
    }

    public function get($id)
    {
        if (isset($this->services[$id]))
        {
            return $this->services[$id];
        }

        if (!isset($this->factories[$id]))
        {
            throw new ServiceNotFoundException(sprintf('The service "%s" does not exist.', $id));
        }

        $service = $this->factories[$id]($this);

        if ($this->shared[$id])
        {
            $this->services[$id] = $service;
            unset($this->factories[$id]);
        }

        return $service;
    }

    public function has($id)
    {
        return isset($this->services[$id]) || isset($this->factories[$id]);
    }
}