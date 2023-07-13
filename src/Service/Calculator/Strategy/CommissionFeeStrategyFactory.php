<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;

class CommissionFeeStrategyFactory
{
    private ConfigurationProviderInterface $configuration;

    public function __construct(ConfigurationProviderInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getStrategy(Operation $operation): CommissionFeeStrategyInterface
    {
        switch (true) {
            case $operation->getOperationType() === Operation::OPERATION_TYPE_DEPOSIT:
                return new DepositCommissionFeeStrategy($this->configuration);
            case $operation->getOperationType() === Operation::OPERATION_TYPE_WITHDRAW
                && $operation->getUserType() === Operation::USER_TYPE_PRIVATE:
                return new WithdrawPrivateCommissionFeeStrategy($this->configuration);
            case $operation->getOperationType() === Operation::OPERATION_TYPE_WITHDRAW
                && $operation->getUserType() === Operation::USER_TYPE_BUSINESS:
                return new WithdrawBusinessCommissionFeeStrategy($this->configuration);
            default:
                return new ZeroCommissionFeeStrategy();
        }
    }
}
