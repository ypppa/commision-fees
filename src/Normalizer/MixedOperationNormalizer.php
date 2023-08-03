<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Evp\Component\Money\MoneyException;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\MixedTypeDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use Throwable;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;
use Ypppa\CommissionFees\Model\Operation\Operation;

class MixedOperationNormalizer implements MixedTypeDenormalizerInterface, TypeAwareInterface
{
    private const COLUMN_NAMES = [
        'date',
        'user_id',
        'user_type',
        'operation_type',
        'operation_amount',
        'operation_currency',
    ];

    public function getType(): string
    {
        return Operation::class;
    }

    /**
     * @param                        $input
     * @param DenormalizationContext $context
     *
     * @return Operation
     * @throws UnsupportedCurrencyException
     * @throws CommissionFeeCalculationFailedException
     */
    public function denormalize($input, DenormalizationContext $context): Operation
    {
        try {
            try {
                $input = array_combine(self::COLUMN_NAMES, $input);
            } catch (Throwable $exception) {
                throw new InvalidDataException('Invalid columns');
            }
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

            $amount = new Money($input['operation_amount'], $input['operation_currency']);

            return new Operation(
                $operationDate,
                $input['user_id'],
                $input['user_type'],
                $input['operation_type'],
                $amount,
            );
        } catch (MoneyException $moneyException) {
            if (str_contains($moneyException->getMessage(), 'Unsupported currency')) {
                throw new UnsupportedCurrencyException($moneyException->getMessage(), $moneyException);
            }
            throw new CommissionFeeCalculationFailedException('', null, $moneyException);
        } catch (InvalidDataException $invalidDataException) {
            throw new InvalidFileFormatException($invalidDataException);
        } catch (Throwable $exception) {
            throw new CommissionFeeCalculationFailedException('', null, $exception);
        }
    }
}
