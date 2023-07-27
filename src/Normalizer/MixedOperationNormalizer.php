<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\MixedTypeDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use Throwable;
use Ypppa\CommissionFees\Model\Operation\Operation;

class MixedOperationNormalizer implements MixedTypeDenormalizerInterface, TypeAwareInterface
{
    public function getType(): string
    {
        return Operation::class;
    }

    /**
     * @param                        $input
     * @param DenormalizationContext $context
     *
     * @return Operation
     * @throws InvalidDataException
     */
    public function denormalize($input, DenormalizationContext $context): Operation
    {
        if (!isset($input['date'])) {
            throw new InvalidDataException('Date is not set');
        }

        if (!isset($input['user_id'])) {
            throw new InvalidDataException('User id is not set');
        }

        if (!isset($input['user_type'])) {
            throw new InvalidDataException('User type is not set');
        }

        if (!isset($input['operation_type'])) {
            throw new InvalidDataException('Operation type is not set');
        }

        if (!isset($input['operation_amount'])) {
            throw new InvalidDataException('Operation amount is not set');
        }

        if (!isset($input['operation_currency'])) {
            throw new InvalidDataException('Operation currency is not set');
        }

        try {
            $operationDate = new DateTimeImmutable($input['date']);
        } catch (Throwable $exception) {
            throw new InvalidDataException('Bad date format', 0, $exception);
        }

        return new Operation(
            $operationDate,
            $input['user_id'],
            $input['user_type'],
            $input['operation_type'],
            new Money($input['operation_amount'], $input['operation_currency']),
        );
    }
}
