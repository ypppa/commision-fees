<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\Operation\OperationDto;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\Manager\OperationManager;
use Ypppa\CommissionFees\Service\OutputWriter\CommissionFeesWriterInterface;
use Ypppa\CommissionFees\Service\Parser\ParserResolver;

class CalculateCommissionFeesCommand extends Command
{
    protected static $defaultDescription = 'Calculate transactions\' commission fees.';
    protected static $defaultName = 'app:calc-commissions';
    private LoggerInterface $logger;
    private ParserResolver $parserResolver;
    private CommissionFeeCalculator $calculator;
    private CommissionFeesWriterInterface $outputWriter;
    private OperationManager $operationManager;

    public function __construct(
        LoggerInterface $logger,
        ParserResolver $parserResolver,
        CommissionFeeCalculator $calculator,
        CommissionFeesWriterInterface $outputWriter,
        OperationManager $operationManager
    ) {

        parent::__construct();
        $this->logger = $logger;
        $this->parserResolver = $parserResolver;
        $this->calculator = $calculator;
        $this->outputWriter = $outputWriter;
        $this->operationManager = $operationManager;
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
            $parser = $this->parserResolver->getParser('operations.' . $format);
            if ($parser === null) {
                throw new CommissionFeeCalculationFailedException('', null, null);
            }
            foreach ($parser->parse($filePath, OperationDto::class) as $operationDto) {
                $operation = $this->operationManager->createFromDto($operationDto);
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
