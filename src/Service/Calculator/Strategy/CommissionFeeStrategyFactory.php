<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\Operation\Operation;

class CommissionFeeStrategyFactory
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getStrategy(Operation $operation): CommissionFeeStrategyInterface
    {
        switch (true) {
            case $operation->getOperationType() === Operation::OPERATION_TYPE_DEPOSIT:
                return new DepositCommissionFeeStrategy($this->config);
            case $operation->getOperationType() === Operation::OPERATION_TYPE_WITHDRAW
                && $operation->getUserType() === Operation::USER_TYPE_PRIVATE:
                return new WithdrawPrivateCommissionFeeStrategy($this->config);
            case $operation->getOperationType() === Operation::OPERATION_TYPE_WITHDRAW
                && $operation->getUserType() === Operation::USER_TYPE_BUSINESS:
                return new WithdrawBusinessCommissionFeeStrategy($this->config);
            default:
                return new ZeroCommissionFeeStrategy();
        }
    }
}
