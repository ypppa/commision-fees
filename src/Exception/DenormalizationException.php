<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;

class DenormalizationException extends CommissionFeeCalculationFailedException
{
    public const DENORMALIZATION_ERROR_CODE = 4;

    public function __construct(?Throwable $previous)
    {
        parent::__construct('Denormalization failed', self::DENORMALIZATION_ERROR_CODE, $previous);
    }
}
