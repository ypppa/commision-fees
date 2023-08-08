<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Manager;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Evp\Component\Money\MoneyException;
use Exception;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;
use Ypppa\CommissionFees\Exception\ValidationException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Operation\OperationDto;

class OperationManager
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param OperationDto $dto
     *
     * @return Operation
     * @throws Exception
     */
    public function createFromDto(OperationDto $dto): Operation
    {
        try {
            $operationAmount = new Money($dto->getAmount(), $dto->getCurrency());

        } catch (MoneyException $moneyException) {
            if (str_contains($moneyException->getMessage(), 'Unsupported currency')) {
                throw new UnsupportedCurrencyException($moneyException->getMessage(), $moneyException);
            }
            throw new CommissionFeeCalculationFailedException('', null, $moneyException);
        }
        $operation = new Operation(
            new DateTimeImmutable($dto->getOperationDate()),
            $dto->getUserId(),
            $dto->getUserType(),
            $dto->getOperationType(),
            $operationAmount
        );

        $violations = $this->validator->validate($operation);
        if ($violations->count() > 0) {
            throw new ValidationException(new ValidationFailedException($operation, $violations));
        }

        return $operation;
    }
}
