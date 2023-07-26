<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;

class CommissionFeeStrategyFactory
{
    private CurrencyConverter $currencyConverter;
    private ConfigurationProviderInterface $configurationProvider;

    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        CurrencyConverter $currencyConverter
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->currencyConverter = $currencyConverter;
    }

    public function getStrategy(Operation $operation): CommissionFeeStrategyInterface
    {
        switch (true) {
            case $operation->isDeposit():
                return new DepositCommissionFeeStrategy($this->configurationProvider->getConfig(), $this->currencyConverter);
            case $operation->isWithdraw() && $operation->isUserPrivate():
                return new WithdrawPrivateCommissionFeeStrategy($this->configurationProvider->getConfig(), $this->currencyConverter);
            case $operation->isWithdraw() && $operation->isUserBusiness():
                return new WithdrawBusinessCommissionFeeStrategy($this->configurationProvider->getConfig(), $this->currencyConverter);
            default:
                return new ZeroCommissionFeeStrategy();
        }
    }
}
