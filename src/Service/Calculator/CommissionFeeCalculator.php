<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Calculator;

use Throwable;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Operation\OperationCollection;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;
use Ypppa\CommissionFees\Service\Calculator\Strategy\CommissionFeeStrategyFactory;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;

class CommissionFeeCalculator
{
    private Config $config;
    private CurrencyConverter $currencyConverter;
    private CommissionFeeStrategyFactory $commissionFeeStrategyFactory;

    public function __construct(
        Config $config,
        CurrencyConverter $currencyConverter,
        CommissionFeeStrategyFactory $commissionFeeStrategyFactory,
    ) {
        $this->config = $config;
        $this->currencyConverter = $currencyConverter;
        $this->commissionFeeStrategyFactory = $commissionFeeStrategyFactory;
    }

    public function calculate(OperationCollection $operationCollection): OperationCollection
    {
        $userCumulativeOperations = null;
        try {
            $iterator = $operationCollection->getIterator();
            foreach ($iterator as $operation) {
                if ($userCumulativeOperations instanceof UserCumulativeOperations
                    && $userCumulativeOperations->getUserId() !== $operation->getUserId()
                    || $userCumulativeOperations === null) {
                    $userCumulativeOperations = new UserCumulativeOperations(
                        $operation->getUserId(),
                        $this->config->getPrivateFreeWithdrawAmount()->getCurrency()
                    );
                }
                $this->handleOne($operation, $userCumulativeOperations);
            }
        } catch (Throwable $exception) {
            // TODO: process exception
        }

        return $operationCollection;
    }

    private function handleOne(Operation $operation, UserCumulativeOperations $userCumulativeOperations): void
    {
        $convertedAmount = $this->currencyConverter->convert(
            $operation->getOperationAmount(),
            $this->config->getPrivateFreeWithdrawAmount()->getCurrency(),
        );
        $userCumulativeOperations->add($convertedAmount);

        $strategy = $this->commissionFeeStrategyFactory->getStrategy($operation);
        $commissionFee = $strategy->calculateCommissionFee(
            $operation->getOperationAmount(),
            $userCumulativeOperations
        );
        $operation->setCommissionFee($commissionFee);
    }
}
