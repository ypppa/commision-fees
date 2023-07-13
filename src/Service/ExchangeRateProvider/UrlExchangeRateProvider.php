<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\ExchangeRateProvider;

class UrlExchangeRateProvider implements ExchangeRateProviderInterface
{
    public function getRate(string $base, string $currency): string
    {
        // TODO: Implement getRate() method.

        return '1';
    }
}
