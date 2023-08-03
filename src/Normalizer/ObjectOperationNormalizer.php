<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Evp\Component\Money\MoneyException;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\ObjectDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
use Throwable;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;
use Ypppa\CommissionFees\Model\Operation\Operation;

class ObjectOperationNormalizer implements ObjectDenormalizerInterface, TypeAwareInterface
{
    public function getType(): string
    {
        return Operation::class;
    }

    /**
     * @param ObjectWrapper          $input
     * @param DenormalizationContext $context
     *
     * @return Operation
     * @throws CommissionFeeCalculationFailedException
     * @throws InvalidFileFormatException
     * @throws UnsupportedCurrencyException
     */
    public function denormalize(ObjectWrapper $input, DenormalizationContext $context): Operation
    {
        try {
            return new Operation(
                new DateTimeImmutable($input->getRequiredString('operation_date')),
                $input->getRequiredString('user_id'),
                $input->getRequiredString('user_type'),
                $input->getRequiredString('operation_type'),
                new Money($input->getRequiredString('amount'), $input->getRequiredString('currency')),
            );
        } catch (MoneyException $moneyException) {
            if (str_contains($moneyException->getMessage(), 'Unsupported currency')) {
                throw new UnsupportedCurrencyException($moneyException->getMessage(), $moneyException);
            }
            throw new CommissionFeeCalculationFailedException('', null, $moneyException);
        } catch (Throwable $invalidDataException) {
            throw new InvalidFileFormatException($invalidDataException);
        }
    }
}
