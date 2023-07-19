<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\InputDataProvider\OperationsDataProviderInterface;
use Ypppa\CommissionFees\Service\OutputWriter\CommissionFeesWriterInterface;

class CalculateCommissionFeesCommand extends Command
{
    protected static $defaultDescription = 'Calculate transactions\' commission fees.';
    protected static $defaultName = 'app:calc-commissions';
    private LoggerInterface $logger;
    private OperationsDataProviderInterface $operationsDataProvider;
    private CommissionFeeCalculator $calculator;
    private CommissionFeesWriterInterface $outputWriter;

    public function __construct(
        LoggerInterface $logger,
        OperationsDataProviderInterface $operationsDataProvider,
        CommissionFeeCalculator $calculator,
        CommissionFeesWriterInterface $outputWriter
    ) {

        parent::__construct();
        $this->logger = $logger;
        $this->operationsDataProvider = $operationsDataProvider;
        $this->calculator = $calculator;
        $this->outputWriter = $outputWriter;
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to calculate transactions\' commission fees.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->operationsDataProvider->load();

            $calculatedOperations = $this->calculator->calculate($this->operationsDataProvider->getOperations());

            $this->outputWriter->write($calculatedOperations);

            return Command::SUCCESS;
        } catch (Throwable $exception) {
            $this->logger->critical($exception);

            return Command::FAILURE;
        }
    }
}
