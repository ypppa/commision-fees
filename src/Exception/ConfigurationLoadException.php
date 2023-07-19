<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;
use UnexpectedValueException;

class ConfigurationLoadException extends UnexpectedValueException
{
    public function __construct(?Throwable $previous)
    {
        parent::__construct('Loading configuration failed', 0, $previous);
    }
}
