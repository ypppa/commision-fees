<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator;

use Throwable;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Operation\OperationCollection;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;
use Ypppa\CommissionFees\Service\Calculator\Strategy\CommissionFeeStrategyFactory;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;

class CommissionFeeCalculator
{
    private ConfigurationProviderInterface $configurationProvider;
    private CurrencyConverter $currencyConverter;
    private CommissionFeeStrategyFactory $commissionFeeStrategyFactory;

    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        CurrencyConverter $currencyConverter,
        CommissionFeeStrategyFactory $commissionFeeStrategyFactory,
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->currencyConverter = $currencyConverter;
        $this->commissionFeeStrategyFactory = $commissionFeeStrategyFactory;
    }

    /**
     * @param OperationCollection $operationCollection
     *
     * @return OperationCollection
     * @throws CommissionFeeCalculationFailedException
     */
    public function calculate(OperationCollection $operationCollection): OperationCollection
    {
        try {
            $operationCollection->sortByUserIdAndDate();
            $userCumulativeOperations = null;

            $iterator = $operationCollection->getIterator();
            foreach ($iterator as $operation) {
                if ((!$userCumulativeOperations instanceof UserCumulativeOperations
                        || $userCumulativeOperations->getUserId() !== $operation->getUserId()
                        || $userCumulativeOperations->getStartOfWeek() !== $operation->getStartOfWeek())
                    && $operation->isWithdraw()
                ) {
                    $userCumulativeOperations = new UserCumulativeOperations(
                        $operation->getUserId(),
                        $this->configurationProvider->getConfig()->getPrivateFreeWithdrawAmount()->getCurrency(),
                        $operation->getDate()
                    );
                }
                $this->handleOne($operation, $userCumulativeOperations);
            }
            $operationCollection->sortByIndex();

            return $operationCollection;
        } catch (Throwable $exception) {
            throw new CommissionFeeCalculationFailedException('', null, $exception);
        }
    }

    private function handleOne(Operation $operation, ?UserCumulativeOperations $userCumulativeOperations): void
    {
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
    }
}
