<?php

declare(strict_types=1);

namespace Aidphp\Di;

use Psr\Container\ContainerInterface;

class CompositeContainer implements ContainerInterface
{
    protected $containers = [];

    public function push(ContainerInterface $container): self
    {
        if ($container instanceof RootContainerAwareInterface)
        {
            $container->setRoot($this);
        }

        array_unshift($this->containers, $container);

        return $this;
    }

    public function pop(): ?ContainerInterface
    {
        $container = array_shift($this->containers);

        if ($container instanceof RootContainerAwareInterface)
        {
            $container->setRoot(null);
        }

        return $container;
    }

    public function get($id)
    {
        foreach ($this->containers as $container)
        {
            if ($container->has($id))
            {
                return $container->get($id);
            }
        }

        throw new NotFoundException($id);
    }

    public function has($id): bool
    {
        foreach ($this->containers as $container)
        {
            if ($container->has($id))
            {
                return true;
            }
        }

        return false;
    }
}