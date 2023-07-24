<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\User;

use DateTimeImmutable;
use Evp\Component\Money\Money;

class UserCumulativeOperations
{
    private string $userId;
    private int $count;
    private Money $amount;
    private DateTimeImmutable $date;

    public function __construct(string $userId, string $currency, DateTimeImmutable $date)
    {
        $this->userId = $userId;
        $this->count = 0;
        $this->amount = Money::createZero($currency);
        $this->date = $date;
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

    public function getStartOfWeek(): string
    {
        return $this->date->modify('Monday this week')->format('YYYY-mm-dd');
    }

    public function add(Money $amount): void
    {
        $this->count++;
        $this->amount = $this->amount->add($amount);
    }
}
