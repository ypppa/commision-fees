<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;
use UnexpectedValueException;

class DateParseException extends UnexpectedValueException
{
    public function __construct(?Throwable $previous)
    {
        parent::__construct('Parsing date from string failed', 0, $previous);
    }
}
