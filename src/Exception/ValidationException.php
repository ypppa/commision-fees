<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Exception;

use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationException extends CommissionFeeCalculationFailedException
{
    public function __construct(
        ValidationFailedException $previous
    ) {
        $message = 'Validation failed: ';
        foreach ($previous->getViolations() as $violation) {
            $message .= $violation->getMessage() . '; ';
        }

        parent::__construct($message, null, $previous);
    }
}
