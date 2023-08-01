<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator;

use Throwable;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\Calculator\Strategy\CommissionFeeStrategyFactory;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;
use Ypppa\CommissionFees\Service\Manager\UserHistoryManager;

class CommissionFeeCalculator
{
    private ConfigurationProviderInterface $configurationProvider;
    private CurrencyConverter $currencyConverter;
    private CommissionFeeStrategyFactory $commissionFeeStrategyFactory;
    private UserHistoryManager $userHistoryManager;

    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        CurrencyConverter $currencyConverter,
        CommissionFeeStrategyFactory $commissionFeeStrategyFactory,
        UserHistoryManager $userHistoryManager
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->currencyConverter = $currencyConverter;
        $this->commissionFeeStrategyFactory = $commissionFeeStrategyFactory;
        $this->userHistoryManager = $userHistoryManager;
    }

    /**
     * @param Operation $operation
     *
     * @return Operation
     * @throws CommissionFeeCalculationFailedException
     */
    public function calculate(Operation $operation): Operation
    {
        try {
            $userCumulativeOperations = $this->userHistoryManager->get(
                $operation->getUserId(),
                $this->configurationProvider->getConfig()->getPrivateFreeWithdrawAmount()->getCurrency(),
                $operation->getDate()
            );

            $strategy = $this->commissionFeeStrategyFactory->getStrategy($operation);
            $commissionFee = $strategy->calculateCommissionFee(
                $operation,
                $userCumulativeOperations
            );
            $operation->setCommissionFee($commissionFee);

            if ($operation->isWithdraw()) {
                $convertedAmount = $this->currencyConverter->convert(
                    $operation->getOperationAmount(),
                    $this->configurationProvider->getConfig()->getPrivateFreeWithdrawAmount()->getCurrency(),
                );
                $userCumulativeOperations->add($convertedAmount);
            }

            return $operation;
        } catch (Throwable $exception) {
            throw new CommissionFeeCalculationFailedException('', null, $exception);
        }
    }
}
