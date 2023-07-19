<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\Config;

use Evp\Component\Money\Money;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Config
{
    private string $baseCurrency;
    private string $depositCommission;
    private Money $privateFreeWithdrawAmount;
    private int $privateFreeWithdrawCount;
    private string $privateWithdrawCommission;
    private string $businessWithdrawCommission;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('baseCurrency', new Assert\NotBlank())
            ->addPropertyConstraint('baseCurrency', new Assert\Type(['type' => ['alpha']]))
            ->addPropertyConstraint('baseCurrency', new Assert\Length(3))
            ->addPropertyConstraint('depositCommission', new Assert\NotBlank())
            ->addPropertyConstraint(
                'depositCommission',
                new Assert\Regex(['pattern' => '/^((?:\d+)(?:\.?\d*))$/'])
            )
            ->addPropertyConstraint('privateFreeWithdrawAmount', new Assert\NotBlank())
            ->addPropertyConstraint('privateFreeWithdrawCount', new Assert\NotBlank())
            ->addPropertyConstraint('privateWithdrawCommission', new Assert\NotBlank())
            ->addPropertyConstraint(
                'privateWithdrawCommission',
                new Assert\Regex(['pattern' => '/^((?:\d+)(?:\.?\d*))$/'])
            )
            ->addPropertyConstraint('businessWithdrawCommission', new Assert\NotBlank())
            ->addPropertyConstraint(
                'businessWithdrawCommission',
                new Assert\Regex(['pattern' => '/^((?:\d+)(?:\.?\d*))$/'])
            )
        ;
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(string $baseCurrency): self
    {
        $this->baseCurrency = $baseCurrency;

        return $this;
    }

    public function getDepositCommission(): string
    {
        return $this->depositCommission;
    }

    public function setDepositCommission(string $depositCommission): self
    {
        $this->depositCommission = $depositCommission;

        return $this;
    }

    public function getPrivateFreeWithdrawAmount(): Money
    {
        return $this->privateFreeWithdrawAmount;
    }

    public function setPrivateFreeWithdrawAmount(Money $privateFreeWithdrawAmount): self
    {
        $this->privateFreeWithdrawAmount = $privateFreeWithdrawAmount;

        return $this;
    }

    public function getPrivateFreeWithdrawCount(): int
    {
        return $this->privateFreeWithdrawCount;
    }

    public function setPrivateFreeWithdrawCount(int $privateFreeWithdrawCount): self
    {
        $this->privateFreeWithdrawCount = $privateFreeWithdrawCount;

        return $this;
    }

    public function getPrivateWithdrawCommission(): string
    {
        return $this->privateWithdrawCommission;
    }

    public function setPrivateWithdrawCommission(string $privateWithdrawCommission): self
    {
        $this->privateWithdrawCommission = $privateWithdrawCommission;

        return $this;
    }

    public function getBusinessWithdrawCommission(): string
    {
        return $this->businessWithdrawCommission;
    }

    public function setBusinessWithdrawCommission(string $businessWithdrawCommission): self
    {
        $this->businessWithdrawCommission = $businessWithdrawCommission;

        return $this;
    }
}
