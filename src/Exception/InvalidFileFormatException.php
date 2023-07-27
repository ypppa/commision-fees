<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Throwable;

class InvalidFileFormatException extends CommissionFeeCalculationFailedException
{
    public const INVALID_FILE_FORMAT_ERROR_CODE = 2;

    public function __construct(?Throwable $previous)
    {
        parent::__construct('Invalid file format', self::INVALID_FILE_FORMAT_ERROR_CODE, $previous);
    }
}
