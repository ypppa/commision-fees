<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Evp\Component\Money\Money;
use Evp\Component\Money\MoneyException;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\MixedTypeDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;

class MoneyNormalizer implements MixedTypeDenormalizerInterface, TypeAwareInterface
{
    public function getType(): string
    {
        return Money::class;
    }

    /**
     * @param                        $input
     * @param DenormalizationContext $context
     *
     * @return Money
     * @throws InvalidDataException
     * @throws UnsupportedCurrencyException
     */
    public function denormalize($input, DenormalizationContext $context): Money
    {
        if (!isset($input['amount'])) {
            throw new InvalidDataException('Amount is not set');
        }

        if (!isset($input['currency'])) {
            throw new InvalidDataException('Currency is not set');
        }

        try {
            return new Money($input['amount'], $input['currency']);
        } catch (MoneyException $moneyException) {
            if (str_contains($moneyException->getMessage(), 'Unsupported currency')) {
                throw new UnsupportedCurrencyException($moneyException->getMessage(), $moneyException);
            }
            throw $moneyException;
        }
    }
}
