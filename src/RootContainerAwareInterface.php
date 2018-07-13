<?php

namespace Aidphp\Di;

use Psr\Container\ContainerInterface;

interface RootContainerAwareInterface
{
    function setRoot(ContainerInterface $container = null);
}