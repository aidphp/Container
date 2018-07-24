<?php

declare(strict_types=1);

namespace Aidphp\Di;

use Psr\Container\ContainerInterface;

interface CompositeContainerInterface extends ContainerInterface
{
    function push(ContainerInterface $container): self;
    function pop(): ?ContainerInterface;
}