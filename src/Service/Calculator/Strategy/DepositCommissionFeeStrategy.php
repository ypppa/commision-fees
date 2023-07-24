<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;

class DepositCommissionFeeStrategy extends AbstractCommissionFeeStrategy implements CommissionFeeStrategyInterface
{
    public function calculateCommissionFee(
        Operation $operation,
        ?UserCumulativeOperations $userCumulativeOperations
    ): Money {
        return $operation->getOperationAmount()->mul($this->config->getDepositCommission())->ceil();
    }
}
