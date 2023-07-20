<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\ExchangeRate;

use DateTimeImmutable;

class ExchangeRates
{
    private string $base;
    private DateTimeImmutable $date;
    /**
     * @var ExchangeRate[]
     */
    private array $rates;

    public function __construct()
    {
        $this->rates = [];
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function setBase(string $base): ExchangeRates
    {
        $this->base = $base;

        return $this;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): ExchangeRates
    {
        $this->date = $date;

        return $this;
    }

    public function getRates(): array
    {
        return $this->rates;
    }

    public function addRate(ExchangeRate $rate): self
    {
        $this->rates[] = $rate;

        return $this;
    }
}
