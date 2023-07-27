<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;

class ExchangeRatesLoadException extends CommissionFeeCalculationFailedException
{
    public const EXCHANGE_RATES_LOAD_ERROR = 5;

    public function __construct(?Throwable $previous)
    {
        parent::__construct('Loading exchange rates failed', self::EXCHANGE_RATES_LOAD_ERROR, $previous);
    }
}
