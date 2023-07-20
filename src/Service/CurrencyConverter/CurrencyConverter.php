<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\CurrencyConverter;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\ExchangeRateProviderInterface;

class CurrencyConverter
{
    private ExchangeRateProviderInterface $exchangeRateProvider;

    public function __construct(ExchangeRateProviderInterface $exchangeRateProvider)
    {
        $this->exchangeRateProvider = $exchangeRateProvider;
    }

    public function convert(Money $money, $currency): Money
    {
        $rate = $this->exchangeRateProvider->getRate($currency, $money->getCurrency());

        return new Money($money->div($rate)->getAmount(), $currency);
    }
}
