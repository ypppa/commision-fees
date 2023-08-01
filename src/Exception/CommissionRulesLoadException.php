<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;

class CommissionRulesLoadException extends CommissionFeeCalculationFailedException
{
    public const COMMISSION_RULES_LOAD_ERROR_CODE = 7;

    public function __construct(?Throwable $previous)
    {
        parent::__construct(
            'Loading commission rules failed',
            self::COMMISSION_RULES_LOAD_ERROR_CODE,
            $previous
        );
    }
}
