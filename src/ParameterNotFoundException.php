<?php

namespace Aidphp\Di;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionParameter;
use Throwable;

class ParameterNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    public function __construct(ReflectionParameter $param, Throwable $previous = null)
    {
        $msg = 'Unable to resolve the parameter ' . ($param->getPosition() + 1) . ' named $' . $param->name . ($param->hasType() ? ' of type ' . $param->getType() : '') .
        ' in ' . (null !== ($class = $param->getDeclaringClass()) ? $class->name . '::' : '') . $param->getDeclaringFunction()->name . '()';

        parent::__construct($msg, 0, $previous);
    }
}