<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\MixedTypeDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use Throwable;
use Ypppa\CommissionFees\Model\Operation\OperationDto;

class MixedOperationDtoNormalizer implements MixedTypeDenormalizerInterface, TypeAwareInterface
{
    private const COLUMN_NAMES = [
        'operation_date',
        'user_id',
        'user_type',
        'operation_type',
        'amount',
        'currency',
    ];

    public function getType(): string
    {
        return OperationDto::class;
    }

    /**
     * @param                        $input
     * @param DenormalizationContext $context
     *
     * @return OperationDto
     * @throws InvalidDataException
     */
    public function denormalize($input, DenormalizationContext $context): OperationDto
    {
        try {
            $input = array_combine(self::COLUMN_NAMES, $input);
        } catch (Throwable) {
            throw new InvalidDataException('Invalid columns');
        }

        return new OperationDto(
            $input['operation_date'] ?? null,
            $input['user_id'] ?? null,
            $input['user_type'] ?? null,
            $input['operation_type'] ?? null,
            $input['amount'] ?? null,
            $input['currency'] ?? null
        );
    }
}
