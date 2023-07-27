<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Exception;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\ObjectDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
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
     * @throws Exception
     */
    public function denormalize(ObjectWrapper $input, DenormalizationContext $context): Operation
    {
        return new Operation(
            new DateTimeImmutable($input->getRequiredString('operation_date')),
            $input->getRequiredString('user_id'),
            $input->getRequiredString('user_type'),
            $input->getRequiredString('operation_type'),
            new Money($input->getRequiredString('amount'), $input->getRequiredString('currency')),
        );
    }
}
