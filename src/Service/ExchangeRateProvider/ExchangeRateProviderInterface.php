<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\ExchangeRateProvider;

interface ExchangeRateProviderInterface
{
    public function getRate(string $base, string $currency): string;
}
