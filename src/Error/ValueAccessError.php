<?php

namespace Oxidize\Error;

use Error;
use Throwable;

final class ValueAccessError extends Error
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
