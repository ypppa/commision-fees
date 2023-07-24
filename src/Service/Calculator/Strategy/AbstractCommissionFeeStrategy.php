<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;

abstract class AbstractCommissionFeeStrategy implements CommissionFeeStrategyInterface
{
    protected Config $config;
    protected CurrencyConverter $currencyConverter;

    public function __construct(Config $config, CurrencyConverter $currencyConverter)
    {
        $this->config = $config;
        $this->currencyConverter = $currencyConverter;
    }

    abstract public function calculateCommissionFee(
        Operation $operation,
        ?UserCumulativeOperations $userCumulativeOperations
    ): Money;
}
