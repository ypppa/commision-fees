<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Model\Operation;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class OperationDto
{
    private ?string $operationDate;
    private ?string $userId;
    private ?string $userType;
    private ?string $operationType;
    private ?string $amount;
    private ?string $currency;

    public function __construct(
        ?string $operationDate,
        ?string $userId,
        ?string $userType,
        ?string $operationType,
        ?string $amount,
        ?string $currency
    ) {
        $this->operationDate = $operationDate;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->operationType = $operationType;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata
            ->addPropertyConstraint(
                'operationDate',
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
                        Operation::USER_TYPE_PRIVATE,
                        Operation::USER_TYPE_BUSINESS,
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
                        Operation::OPERATION_TYPE_DEPOSIT,
                        Operation::OPERATION_TYPE_WITHDRAW,
                    ],
                    null, null, null, null, null, null,
                    'operationType is invalid'
                )
            )
            ->addPropertyConstraint(
                'amount',
                new Assert\NotBlank(null, 'amount field is required')
            )
            ->addPropertyConstraint(
                'currency',
                new Assert\NotBlank(null, 'currency field is required')
            )
        ;
    }

    public function getOperationDate(): ?string
    {
        return $this->operationDate;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    public function getOperationType(): ?string
    {
        return $this->operationType;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }
}
