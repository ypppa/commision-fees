<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\MixedTypeDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use Ypppa\CommissionFees\Model\Config\Config;

class MixedConfigNormalizer implements MixedTypeDenormalizerInterface, TypeAwareInterface
{
    public function getType(): string
    {
        return Config::class;
    }

    /**
     * @param                        $input
     * @param DenormalizationContext $context
     *
     * @return Config
     * @throws InvalidDataException
     */
    public function denormalize($input, DenormalizationContext $context): Config
    {
        if (!isset($input['base_currency'])) {
            throw new InvalidDataException('Base currency is not set');
        }

        return (new Config())->setBaseCurrency($input['base_currency']);
    }
}
