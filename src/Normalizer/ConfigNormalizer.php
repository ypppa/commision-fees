<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Evp\Component\Money\Money;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\MixedTypeDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use Ypppa\CommissionFees\Model\Config\Config;

class ConfigNormalizer implements MixedTypeDenormalizerInterface, TypeAwareInterface
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

        if (!isset($input['deposit_commission'])) {
            throw new InvalidDataException('Deposit commission is not set');
        }

        if (!isset($input['private_free_withdraw_amount'])) {
            throw new InvalidDataException('Private free withdraw amount is not set');
        }

        if (!isset($input['private_free_withdraw_count'])) {
            throw new InvalidDataException('Private free withdraw count is not set');
        }

        if (!isset($input['private_withdraw_commission'])) {
            throw new InvalidDataException('Private withdraw commission is not set');
        }

        if (!isset($input['business_withdraw_commission'])) {
            throw new InvalidDataException('Business withdraw commission is not set');
        }

        return (new Config())
            ->setBaseCurrency($input['base_currency'])
            ->setDepositCommission($input['deposit_commission'])
            ->setPrivateFreeWithdrawAmount(
                $context->denormalize($input['private_free_withdraw_amount'], Money::class)
            )
            ->setPrivateFreeWithdrawCount($input['private_free_withdraw_count'])
            ->setPrivateWithdrawCommission($input['private_withdraw_commission'])
            ->setBusinessWithdrawCommission($input['business_withdraw_commission'])
        ;
    }
}
