#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Ypppa\CommissionFees\Command\CalculateCommissionFeesCommand;
use Ypppa\CommissionFees\Normalizer\DenormalizerFactory;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\Calculator\Strategy\CommissionFeeStrategyFactory;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\UrlExchangeRateProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\OperationsDataProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\YamlConfigurationProvider;
use Ypppa\CommissionFees\Service\OutputWriter\ConsoleCommissionFeesWriter;
use Ypppa\CommissionFees\Service\Parser\CsvOperationsParser;
use Ypppa\CommissionFees\Validator\MetadataValidatorFactory;

$application = new Application('commission-fees', '1.0.0');

$logger = new ConsoleLogger(new ConsoleOutput());
$denormalizer = (new DenormalizerFactory())->createDenormalizer();
$configurationProvider = new YamlConfigurationProvider(
    $denormalizer,
    (new MetadataValidatorFactory())->createValidator(),
    'config.yaml'
);

$calculator = new CommissionFeeCalculator(
    $configurationProvider->getConfig(),
    new CurrencyConverter(
        new UrlExchangeRateProvider(
            $denormalizer,
            'https://developers.paysera.com/tasks/api/currency-exchange-rates'
        )
    ),
    new CommissionFeeStrategyFactory($configurationProvider->getConfig())
);

$operationsDataProvider = new OperationsDataProvider(
    new CsvOperationsParser(
        $denormalizer,
        (new MetadataValidatorFactory())->createValidator(),
        'operations.csv',
        $logger
    )
);

$command = new CalculateCommissionFeesCommand(
    $logger,
    $operationsDataProvider,
    $calculator,
    new ConsoleCommissionFeesWriter()
);

$application->add($command);

$application->setDefaultCommand($command->getName(), true);
$application->run();
