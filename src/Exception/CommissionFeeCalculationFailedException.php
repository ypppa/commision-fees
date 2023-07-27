<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Exception;
use Throwable;

class CommissionFeeCalculationFailedException extends Exception
{
    public const UNEXPECTED_ERROR_CODE = 255;

    public function __construct(string $message, ?int $code, ?Throwable $previous)
    {
        parent::__construct(
            'Commission fees calculation failed: ' . $message,
            $code ?? self::UNEXPECTED_ERROR_CODE, $previous
        );
    }
}
