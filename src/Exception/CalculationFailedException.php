<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use RuntimeException;
use Throwable;

class CalculationFailedException extends RuntimeException
{
    public function __construct(?Throwable $previous)
    {
        parent::__construct('Commission fees calculation failed', 0, $previous);
    }
}
