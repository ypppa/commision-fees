<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\Operation;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

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
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata
            ->addPropertyConstraint(
                'date',
                new Assert\NotBlank(null, 'date field is required')
            )
            ->addPropertyConstraint(
                'userId',
                new Assert\NotBlank(null, 'userId field is required')
            )
            ->addPropertyConstraint(
                'userType',
                new Assert\NotBlank(null, 'userType field is required')
            )
            ->addPropertyConstraint(
                'userType',
                new Assert\Choice(
                    [
                        self::USER_TYPE_PRIVATE,
                        self::USER_TYPE_BUSINESS,
                    ],
                    null, null, null, null, null, null,
                    'userType is invalid'
                )
            )
            ->addPropertyConstraint(
                'operationType',
                new Assert\NotBlank(null, 'operationType field is required')
            )
            ->addPropertyConstraint(
                'operationType',
                new Assert\Choice(
                    [
                        self::OPERATION_TYPE_DEPOSIT,
                        self::OPERATION_TYPE_WITHDRAW,
                    ],
                    null, null, null, null, null, null,
                    'operationType is invalid'
                )
            )
            ->addPropertyConstraint(
                'operationAmount',
                new Assert\NotBlank(null, 'operationAmount field is required')
            )
        ;
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
}
