<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\Rule;

use Evp\Component\Money\Money;

class CommissionFeeRule
{
    /**
     * @var string[]|null
     */
    private ?array $userId;
    private ?string $userType;
    private ?string $operationType;
    private ?int $freeOperationsCountLimit;
    private ?Money $freeOperationsAmountLimit;
    private string $commission;
    private ?Money $commissionFeeMin;
    private ?Money $commissionFeeMax;

    public function __construct()
    {
        $this->userId = null;
        $this->userType = null;
        $this->operationType = null;
        $this->freeOperationsCountLimit = null;
        $this->freeOperationsAmountLimit = null;
        $this->commission = '0';
        $this->commissionFeeMin = null;
        $this->commissionFeeMax = null;
    }

    /**
     * @return string[]|null
     */
    public function getUserId(): ?array
    {
        return $this->userId;
    }

    /**
     * @param string[]|null $userId
     *
     * @return $this
     */
    public function setUserId(?array $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    public function setUserType(?string $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getOperationType(): ?string
    {
        return $this->operationType;
    }

    public function setOperationType(?string $operationType): self
    {
        $this->operationType = $operationType;

        return $this;
    }

    public function getFreeOperationsCountLimit(): ?int
    {
        return $this->freeOperationsCountLimit;
    }

    public function setFreeOperationsCountLimit(?int $freeOperationsCountLimit): self
    {
        $this->freeOperationsCountLimit = $freeOperationsCountLimit;

        return $this;
    }

    public function getFreeOperationsAmountLimit(): ?Money
    {
        return $this->freeOperationsAmountLimit;
    }

    public function setFreeOperationsAmountLimit(?Money $freeOperationsAmountLimit): self
    {
        $this->freeOperationsAmountLimit = $freeOperationsAmountLimit;

        return $this;
    }

    public function getCommission(): string
    {
        return $this->commission;
    }

    public function setCommission(string $commission): self
    {
        $this->commission = $commission;

        return $this;
    }

    public function getCommissionFeeMin(): ?Money
    {
        return $this->commissionFeeMin;
    }

    public function setCommissionFeeMin(?Money $commissionFeeMin): self
    {
        $this->commissionFeeMin = $commissionFeeMin;

        return $this;
    }

    public function getCommissionFeeMax(): ?Money
    {
        return $this->commissionFeeMax;
    }

    public function setCommissionFeeMax(?Money $commissionFeeMax): self
    {
        $this->commissionFeeMax = $commissionFeeMax;

        return $this;
    }
}
