<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use RuntimeException;
use Throwable;

class ExchangeRatesLoadException extends RuntimeException
{
    public function __construct(?Throwable $previous)
    {
        parent::__construct('Loading exchange rates failed', 0, $previous);
    }
}
