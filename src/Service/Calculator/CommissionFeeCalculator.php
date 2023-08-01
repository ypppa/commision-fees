<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator;

use Evp\Component\Money\Money;
use Throwable;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\InputDataProvider\CommissionRulesProviderInterface;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;
use Ypppa\CommissionFees\Service\Manager\UserHistoryManager;

class CommissionFeeCalculator
{
    private ConfigurationProviderInterface $configurationProvider;
    private CurrencyConverter $currencyConverter;
    private CommissionRulesProviderInterface $commissionRulesProvider;
    private UserHistoryManager $userHistoryManager;

    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        CurrencyConverter $currencyConverter,
        CommissionRulesProviderInterface $commissionRulesProvider,
        UserHistoryManager $userHistoryManager
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->currencyConverter = $currencyConverter;
        $this->commissionRulesProvider = $commissionRulesProvider;
        $this->userHistoryManager = $userHistoryManager;
    }

    /**
     * @param Operation $operation
     *
     * @return Money
     * @throws CommissionFeeCalculationFailedException
     */
    public function calculate(Operation $operation): Money
    {
        try {
            $rule = $this->commissionRulesProvider->getRule($operation);

            $userCumulativeOperations = $this->getUserCumulativeOperations($rule, $operation);

            $convertedAmount = $this->currencyConverter->convert(
                $operation->getOperationAmount(),
                $userCumulativeOperations->getAmount()->getCurrency(),
            );

            $commissionFee = $this->getCommissionFee($rule, $userCumulativeOperations, $operation, $convertedAmount);
            $commissionFee = $this->minFeeCheck($rule, $commissionFee);
            $commissionFee = $this->maxFeeCheck($rule, $commissionFee);

            $operation->setCommissionFee($commissionFee);

            if ($rule->getFreeOperationsCountLimit() !== null || $rule->getFreeOperationsAmountLimit() !== null) {
                $userCumulativeOperations->add($convertedAmount);
            }

            return $commissionFee;
        } catch (Throwable $exception) {
            throw new CommissionFeeCalculationFailedException('', null, $exception);
        }
    }

    private function getUserCumulativeOperations(
        CommissionFeeRule $rule,
        Operation $operation
    ): UserCumulativeOperations {
        if ($rule->getFreeOperationsAmountLimit() !== null) {
            return $this->userHistoryManager->get(
                $operation->getUserId(),
                $rule->getFreeOperationsAmountLimit()->getCurrency(),
                $operation->getDate()
            );
        }

        return $this->userHistoryManager->get(
            $operation->getUserId(),
            $this->configurationProvider->getConfig()->getBaseCurrency(),
            $operation->getDate()
        );
    }

    private function getCommissionFee(
        CommissionFeeRule $rule,
        UserCumulativeOperations $userCumulativeOperations,
        Operation $operation,
        Money $convertedAmount
    ): Money {
        if (
            $userCumulativeOperations->getCount() >= $rule->getFreeOperationsCountLimit()
            || $userCumulativeOperations->getAmount()
                ->isGte($rule->getFreeOperationsAmountLimit())
        ) {
            return $operation->getOperationAmount()
                ->mul($rule->getCommission())
                ->ceil()
            ;
        }

        if ($userCumulativeOperations->getAmount()
            ->add($convertedAmount)
            ->isGt($rule->getFreeOperationsAmountLimit())
        ) {
            $overflowAmount = $userCumulativeOperations->getAmount()
                ->add($convertedAmount)
                ->sub($rule->getFreeOperationsAmountLimit())
            ;

            return $this->currencyConverter
                ->convert($overflowAmount, $operation->getOperationAmount()->getCurrency())
                ->mul($rule->getCommission())
                ->ceil()
            ;
        }

        return Money::createZero($operation->getOperationAmount()->getCurrency());
    }

    private function minFeeCheck(CommissionFeeRule $rule, Money $commissionFee): Money
    {
        if ($rule->getCommissionFeeMin() !== null) {
            $minFeeConvertedAmount = $this->currencyConverter->convert(
                $rule->getCommissionFeeMin(),
                $commissionFee->getCurrency(),
            );

            if ($commissionFee->isLt($minFeeConvertedAmount)) {
                $commissionFee = $minFeeConvertedAmount;
            }
        }

        return $commissionFee;
    }

    private function maxFeeCheck(CommissionFeeRule $rule, Money $commissionFee): Money
    {
        if ($rule->getCommissionFeeMax() !== null) {
            $maxFeeConvertedAmount = $this->currencyConverter->convert(
                $rule->getCommissionFeeMax(),
                $commissionFee->getCurrency(),
            );

            if ($commissionFee->isGt($maxFeeConvertedAmount)) {
                $commissionFee = $maxFeeConvertedAmount;
            }
        }

        return $commissionFee;
    }
}
