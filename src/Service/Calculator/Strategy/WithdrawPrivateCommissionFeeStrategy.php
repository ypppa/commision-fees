<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator\Strategy;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;

class WithdrawPrivateCommissionFeeStrategy extends AbstractCommissionFeeStrategy
    implements CommissionFeeStrategyInterface
{
    public function calculateCommissionFee(
        Operation $operation,
        ?UserCumulativeOperations $userCumulativeOperations
    ): Money {
        if ($userCumulativeOperations->getCount() >= $this->config->getPrivateFreeWithdrawCount()
            || $userCumulativeOperations->getAmount()->isGt($this->config->getPrivateFreeWithdrawAmount())
        ) {
            return $operation->getOperationAmount()->mul($this->config->getPrivateWithdrawCommission())->ceil();
        }

        $convertedAmount = $this->currencyConverter->convert(
            $operation->getOperationAmount(),
            $this->config->getPrivateFreeWithdrawAmount()->getCurrency()
        );

        if ($userCumulativeOperations->getAmount()
            ->add($convertedAmount)
            ->isGt($this->config->getPrivateFreeWithdrawAmount())
        ) {
            $overflowAmount = $userCumulativeOperations->getAmount()
                ->add($convertedAmount)
                ->sub($this->config->getPrivateFreeWithdrawAmount())
            ;

            return $this->currencyConverter
                ->convert($overflowAmount, $operation->getOperationAmount()->getCurrency())
                ->mul($this->config->getPrivateWithdrawCommission())
                ->ceil()
            ;
        }

        return Money::createZero($operation->getOperationAmount()->getCurrency())->ceil();
    }
}
