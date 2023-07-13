<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;

abstract class AbstractCommissionFeeStrategy implements CommissionFeeStrategyInterface
{
    protected ConfigurationProviderInterface $configuration;

    public function __construct(ConfigurationProviderInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    abstract public function calculateCommissionFee(
        Money $operationAmount,
        UserCumulativeOperations $userCumulativeOperations
    ): Money;
}
