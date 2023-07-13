<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\Calculator\Strategy\CommissionFeeStrategyFactory;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\UrlExchangeRateProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\CsvOperationsDataProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\YamlConfigurationProvider;
use Ypppa\CommissionFees\Service\OutputWriter\ConsoleCommissionFeesWriter;

class CalculateCommissionFeesCommand extends Command
{
    protected static $defaultDescription = 'Calculate transactions\' commission fees.';
    protected static $defaultName = 'app:calc-commissions';

    protected function configure(): void
    {
        $this->setHelp('This command allows you to calculate transactions\' commission fees.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $operationsDataProvider = new CsvOperationsDataProvider();
            $operationsDataProvider->load();
            $configurationProvider = new YamlConfigurationProvider('config.yaml');
            $configurationProvider->load();
            $exchangeRateProvider = new UrlExchangeRateProvider();
            $currencyConverter = new CurrencyConverter($exchangeRateProvider);
            $commissionFeeStrategyFactory = new CommissionFeeStrategyFactory($configurationProvider);

            $calculator = new CommissionFeeCalculator(
                $configurationProvider,
                $currencyConverter,
                $commissionFeeStrategyFactory,
            );

            $calculatedOperations = $calculator->calculate($operationsDataProvider->getOperations());

            $outputWriter = new ConsoleCommissionFeesWriter();
            $outputWriter->write($calculatedOperations);

            return Command::SUCCESS;
        } catch (Throwable $exception) {
            return Command::FAILURE;
        }
    }
}
