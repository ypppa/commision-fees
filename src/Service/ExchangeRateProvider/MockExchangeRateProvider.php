<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\ExchangeRateProvider;

use Ypppa\CommissionFees\Exception\RateNotFoundException;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRates;

class MockExchangeRateProvider implements ExchangeRateProviderInterface
{
    private ExchangeRates $exchangeRates;

    public function __construct(ExchangeRates $exchangeRates)
    {
        $this->exchangeRates = $exchangeRates;
    }

    /**
     * @param string $base
     * @param string $currency
     *
     * @return string
     * @throws RateNotFoundException
     */
    public function getRate(string $base, string $currency): string
    {
        foreach ($this->exchangeRates->getRates() as $exchangeRate) {
            if ($exchangeRate->getCurrency() === $currency) {
                return $exchangeRate->getRate();
            }
        }

        throw new RateNotFoundException(null);
    }
}
