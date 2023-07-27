<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
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
        $this
            ->setHelp('This command allows you to calculate transactions\' commission fees.')
            ->addArgument('file_path', InputArgument::REQUIRED, 'Operations data file path')
            ->addArgument('format', InputArgument::REQUIRED, 'Operations file format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $filePath = $input->getArgument('file_path');
            $format = $input->getArgument('format');
            $calculatedOperations = $this->calculator->calculate(
                $this->operationsDataProvider->getOperations($filePath, $format)
            );

            $this->outputWriter->write($calculatedOperations);

            return Command::SUCCESS;
        } catch (CommissionFeeCalculationFailedException $handledException) {
            $this->logger->critical($handledException);
            $output->write($handledException->getMessage());

            return $handledException->getCode();
        }
    }
}
