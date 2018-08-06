<?php

declare(strict_types=1);

namespace Aidphp\Di;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    public function __construct($id)
    {
        parent::__construct('Unable to resolve "' . $id . '"');
    }
}