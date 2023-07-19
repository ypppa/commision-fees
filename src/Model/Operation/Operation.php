<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\Operation;

use DateTimeImmutable;
use Evp\Component\Money\Money;

class Operation
{
    public const USER_TYPE_PRIVATE = 'private';
    public const USER_TYPE_BUSINESS = 'business';
    public const OPERATION_TYPE_DEPOSIT = 'deposit';
    public const OPERATION_TYPE_WITHDRAW = 'withdraw';

    private DateTimeImmutable $date;
    private string $userId;
    private string $userType;
    private string $operationType;
    private Money $operationAmount;
    private ?Money $commissionFee;

    public function __construct(
        DateTimeImmutable $date,
        string $userId,
        string $userType,
        string $operationType,
        Money $operationAmount,
    ) {
        $this->date = $date;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->operationType = $operationType;
        $this->operationAmount = $operationAmount;
        $this->commissionFee = null;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function getOperationAmount(): Money
    {
        return $this->operationAmount;
    }

    public function setCommissionFee(?Money $commissionFee): self
    {
        $this->commissionFee = $commissionFee;

        return $this;
    }

    public function getCommissionFee(): ?Money
    {
        return $this->commissionFee;
    }
}
