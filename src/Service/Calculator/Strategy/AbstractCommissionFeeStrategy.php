<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;

abstract class AbstractCommissionFeeStrategy implements CommissionFeeStrategyInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    abstract public function calculateCommissionFee(
        Money $operationAmount,
        UserCumulativeOperations $userCumulativeOperations
    ): Money;
}
