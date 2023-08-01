<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Evp\Component\Money\Money;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\ObjectDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
use Throwable;
use Ypppa\CommissionFees\Exception\DenormalizationException;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;

class ObjectCommissionRuleNormalizer implements ObjectDenormalizerInterface, TypeAwareInterface
{
    public function getType(): string
    {
        return CommissionFeeRule::class;
    }

    /**
     * @param ObjectWrapper          $input
     * @param DenormalizationContext $context
     *
     * @return CommissionFeeRule
     * @throws DenormalizationException
     */
    public function denormalize(ObjectWrapper $input, DenormalizationContext $context): CommissionFeeRule
    {
        try {
            $operationsAmountLimit = null;
            if ($input->getObject('free_operations_amount_limit') !== null) {
                $operationsAmountLimit = new Money(
                    $input->getObject('free_operations_amount_limit')->getRequiredString('amount'),
                    $input->getObject('free_operations_amount_limit')->getRequiredString('currency')
                );
            }
            $commissionFeeMin = null;
            if ($input->getObject('commission_fee_min') !== null) {
                $commissionFeeMin = new Money(
                    $input->getObject('commission_fee_min')->getRequiredString('amount'),
                    $input->getObject('commission_fee_min')->getRequiredString('currency')
                );
            }
            $commissionFeeMax = null;
            if ($input->getObject('commission_fee_max') !== null) {
                $commissionFeeMax = new Money(
                    $input->getObject('commission_fee_max')->getRequiredString('amount'),
                    $input->getObject('commission_fee_max')->getRequiredString('currency')
                );
            }

            return (new CommissionFeeRule())
                ->setUserId($input->getArray('user_id'))
                ->setUserType($input->getString('user_type'))
                ->setOperationType($input->getString('operation_type'))
                ->setFreeOperationsCountLimit($input->getInt('free_operations_count_limit'))
                ->setFreeOperationsAmountLimit($operationsAmountLimit)
                ->setCommission($input->getRequiredString('commission'))
                ->setCommissionFeeMin($commissionFeeMin)
                ->setCommissionFeeMax($commissionFeeMax)
            ;
        } catch (Throwable $exception) {
            throw new DenormalizationException($exception);
        }
    }
}
