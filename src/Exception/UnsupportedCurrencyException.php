<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;

class UnsupportedCurrencyException extends CommissionFeeCalculationFailedException
{
    public const UNSUPPORTED_CURRENCY_ERROR_CODE = 1;

    public function __construct(?string $message, ?Throwable $previous)
    {
        parent::__construct(
            $message ?? 'Unsupported currency',
            self::UNSUPPORTED_CURRENCY_ERROR_CODE, $previous
        );
    }
}
