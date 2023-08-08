<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\ObjectDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
use Ypppa\CommissionFees\Model\Operation\OperationDto;

class ObjectOperationDtoNormalizer implements ObjectDenormalizerInterface, TypeAwareInterface
{
    public function getType(): string
    {
        return OperationDto::class;
    }

    /**
     * @param ObjectWrapper          $input
     * @param DenormalizationContext $context
     *
     * @return OperationDto
     */
    public function denormalize(ObjectWrapper $input, DenormalizationContext $context): OperationDto
    {
        return new OperationDto(
            $input->getString('operation_date'),
            $input->getString('user_id'),
            $input->getString('user_type'),
            $input->getString('operation_type'),
            $input->getString('amount'),
            $input->getString('currency'),
        );
    }
}
