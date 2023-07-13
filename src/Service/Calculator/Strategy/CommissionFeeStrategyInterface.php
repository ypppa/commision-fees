<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;

interface CommissionFeeStrategyInterface
{
    public function calculateCommissionFee(
        Money $operationAmount,
        UserCumulativeOperations $userCumulativeOperations
    ): Money;
}
