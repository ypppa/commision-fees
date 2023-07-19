<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

interface ValidatorFactoryInterface
{
    public function createValidator(): ValidatorInterface;
}
