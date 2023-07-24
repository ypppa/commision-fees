<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\CurrencyConverter;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\ExchangeRateProviderInterface;

class CurrencyConverter
{
    private ExchangeRateProviderInterface $exchangeRateProvider;
    private Config $config;

    public function __construct(ExchangeRateProviderInterface $exchangeRateProvider, Config $config)
    {
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->config = $config;
    }

    public function convert(Money $money, string $currency): Money
    {
        if ($money->getCurrency() === $currency) {
            return $money;
        }

        if ($money->getCurrency() === $this->config->getBaseCurrency()) {
            $rate = $this->exchangeRateProvider->getRate($this->config->getBaseCurrency(), $currency);

            return new Money($money->mul($rate)->getAmount(), $currency);
        }

        if ($currency === $this->config->getBaseCurrency()) {
            $rate = $this->exchangeRateProvider->getRate($this->config->getBaseCurrency(), $money->getCurrency());

            return new Money($money->div($rate)->getAmount(), $currency);
        }

        $crossRateFrom = $this->exchangeRateProvider->getRate($this->config->getBaseCurrency(), $currency);
        $crossRateTo = $this->exchangeRateProvider->getRate($this->config->getBaseCurrency(), $money->getCurrency());

        return new Money($money->mul($crossRateFrom)->div($crossRateTo)->getAmount(), $currency);
    }
}
