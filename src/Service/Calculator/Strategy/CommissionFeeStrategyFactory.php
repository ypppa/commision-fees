<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;

class CommissionFeeStrategyFactory
{
    private Config $config;
    private CurrencyConverter $currencyConverter;

    public function __construct(Config $config, CurrencyConverter $currencyConverter)
    {
        $this->config = $config;
        $this->currencyConverter = $currencyConverter;
    }

    public function getStrategy(Operation $operation): CommissionFeeStrategyInterface
    {
        switch (true) {
            case $operation->isDeposit():
                return new DepositCommissionFeeStrategy($this->config, $this->currencyConverter);
            case $operation->isWithdraw() && $operation->isUserPrivate():
                return new WithdrawPrivateCommissionFeeStrategy($this->config, $this->currencyConverter);
            case $operation->isWithdraw() && $operation->isUserBusiness():
                return new WithdrawBusinessCommissionFeeStrategy($this->config, $this->currencyConverter);
            default:
                return new ZeroCommissionFeeStrategy();
        }
    }
}
