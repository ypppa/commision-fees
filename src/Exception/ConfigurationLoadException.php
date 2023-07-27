<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;

class ConfigurationLoadException extends CommissionFeeCalculationFailedException
{
    public const CONFIGURATION_LOAD_ERROR_CODE = 3;

    public function __construct(?Throwable $previous)
    {
        parent::__construct(
            'Loading configuration failed',
            self::CONFIGURATION_LOAD_ERROR_CODE, $previous
        );
    }
}
