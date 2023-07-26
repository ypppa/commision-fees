<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\CurrencyConverter;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\ExchangeRateProviderInterface;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;

class CurrencyConverter
{
    private ExchangeRateProviderInterface $exchangeRateProvider;
    private ConfigurationProviderInterface $configurationProvider;

    public function __construct(
        ExchangeRateProviderInterface $exchangeRateProvider,
        ConfigurationProviderInterface $configurationProvider
    ) {
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->configurationProvider = $configurationProvider;
    }

    public function convert(Money $money, string $currency): Money
    {
        $config = $this->configurationProvider->getConfig();
        if ($money->getCurrency() === $currency) {
            return $money;
        }

        if ($money->getCurrency() === $config->getBaseCurrency()) {
            $rate = $this->exchangeRateProvider->getRate($config->getBaseCurrency(), $currency);

            return new Money($money->mul($rate)->getAmount(), $currency);
        }

        if ($currency === $config->getBaseCurrency()) {
            $rate = $this->exchangeRateProvider->getRate($config->getBaseCurrency(), $money->getCurrency());

            return new Money($money->div($rate)->getAmount(), $currency);
        }

        $crossRateFrom = $this->exchangeRateProvider->getRate($config->getBaseCurrency(), $currency);
        $crossRateTo = $this->exchangeRateProvider->getRate($config->getBaseCurrency(), $money->getCurrency());

        return new Money($money->mul($crossRateFrom)->div($crossRateTo)->getAmount(), $currency);
    }
}
