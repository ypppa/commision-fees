<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\User;

use Evp\Component\Money\Money;

class UserCumulativeOperations
{
    private string $userId;
    private int $count;
    private Money $amount;

    public function __construct(string $userId, string $currency)
    {
        $this->userId = $userId;
        $this->count = 0;
        $this->amount = Money::createZero($currency);
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function add(Money $amount): void
    {
        $this->count++;
        $this->amount->add($amount);
    }
}
