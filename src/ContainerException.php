<?php

namespace Aidphp\Di;

use RuntimeException;
use Psr\Container\ContainerExceptionInterface;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}