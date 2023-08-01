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
use Ypppa\CommissionFees\Service\OutputWriter\CommissionFeesWriterInterface;
use Ypppa\CommissionFees\Service\Parser\OperationsParserFactory;

class CalculateCommissionFeesCommand extends Command
{
    protected static $defaultDescription = 'Calculate transactions\' commission fees.';
    protected static $defaultName = 'app:calc-commissions';
    private LoggerInterface $logger;
    private OperationsParserFactory $parserFactory;
    private CommissionFeeCalculator $calculator;
    private CommissionFeesWriterInterface $outputWriter;

    public function __construct(
        LoggerInterface $logger,
        OperationsParserFactory $operationsParser,
        CommissionFeeCalculator $calculator,
        CommissionFeesWriterInterface $outputWriter
    ) {

        parent::__construct();
        $this->logger = $logger;
        $this->parserFactory = $operationsParser;
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
            $parser = $this->parserFactory->getParser($filePath, $format);
            foreach ($parser->parse() as $operation) {
                $commissionFee = $this->calculator->calculate($operation);

                $this->outputWriter->write($commissionFee);
            }

            return Command::SUCCESS;
        } catch (CommissionFeeCalculationFailedException $handledException) {
            $this->logger->critical($handledException);
            $output->write($handledException->getMessage());

            return $handledException->getCode();
        }
    }
}
