<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;

class DepositCommissionFeeStrategy extends AbstractCommissionFeeStrategy implements CommissionFeeStrategyInterface
{
    public function calculateCommissionFee(
        Money $operationAmount,
        UserCumulativeOperations $userCumulativeOperations
    ): Money {
        // TODO: Implement calculateCommissionFee() method.

        return new Money();
    }
}
