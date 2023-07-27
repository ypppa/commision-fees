<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;

class RateNotFoundException extends CommissionFeeCalculationFailedException
{
    public const RATE_NOT_FOUND_ERROR_CODE = 6;

    public function __construct(?Throwable $previous)
    {
        parent::__construct(
            'There is no rate for requested currency pair',
            self::RATE_NOT_FOUND_ERROR_CODE, $previous
        );
    }
}
