<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;
use UnexpectedValueException;

class RateNotFoundException extends UnexpectedValueException
{
    public function __construct(?Throwable $previous)
    {
        parent::__construct('There is no rate for requested currency pair', 0, $previous);
    }
}
