<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\ExchangeRate;

class ExchangeRate
{
    private string $currency;
    private string $rate;

    public function __construct(string $currency, string $rate)
    {
        $this->currency = $currency;
        $this->rate = $rate;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getRate(): string
    {
        return $this->rate;
    }
}
