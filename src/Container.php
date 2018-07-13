<?php

declare(strict_types=1);

namespace Aidphp\Di;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface, RootContainerAwareInterface
{
    protected $root;
    protected $data      = [];
    protected $factories = [];
    protected $shared    = [];

    public function __construct(array $data = [], ContainerInterface $container = null)
    {
        $this->data = $data;
        $this->setRoot($container);
    }

    public function setRoot(ContainerInterface $container = null): self
    {
        if ($this->root)
        {
            unset($this->data[get_class($this->root)]);
        }

        $this->root = $container ?? $this;

        $this->data[ContainerInterface::class] = $this->root;
        $this->data[get_class($this->root)] = $this->root;
        return $this;
    }

    public function register($id, callable $factory, bool $shared = true): self
    {
        $this->factories[$id] = $factory;
        $this->shared[$id]    = $shared;
        return $this;
    }

    public function set($id, $value): self
    {
        if (isset($this->data[$id]))
        {
            throw new ContainerException('Identifier "' . $id . '" is already defined, cannot replace it');
        }

        $this->data[$id] = $value;
        return $this;
    }

    public function get($id)
    {
        if (isset($this->data[$id]) || array_key_exists($id, $this->data))
        {
            return $this->data[$id];
        }

        if (! isset($this->factories[$id]))
		{
		    throw new NotFoundException($id);
		}

		$value = $this->factories[$id]($this->root ?? $this);

		if ($this->shared[$id])
		{
		    $this->data[$id] = $value;
		    unset($this->factories[$id]);
		}

		return $value;
    }

    public function has($id): bool
    {
        return isset($this->data[$id]) || isset($this->factories[$id]) || array_key_exists($id, $this->data);
    }
}