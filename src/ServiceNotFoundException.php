<?php

namespace Aidphp\Container;

use Exception;
use Interop\Container\Exception\NotFoundException;

class ServiceNotFoundException extends Exception implements NotFoundException
{
}