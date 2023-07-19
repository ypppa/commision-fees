#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Ypppa\CommissionFees\Command\CalculateCommissionFeesCommand;
use Ypppa\CommissionFees\Normalizer\MixedDenormalizerFactory;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\Calculator\Strategy\CommissionFeeStrategyFactory;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\UrlExchangeRateProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\CsvOperationsDataProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\YamlConfigurationProvider;
use Ypppa\CommissionFees\Service\OutputWriter\ConsoleCommissionFeesWriter;
use Ypppa\CommissionFees\Validator\MetadataValidatorFactory;

$application = new Application('commission-fees', '1.0.0');

$configurationProvider = new YamlConfigurationProvider(
    (new MixedDenormalizerFactory())->createConfigDenormalizer(),
    (new MetadataValidatorFactory())->createValidator(),
    'config.yaml'
);

$calculator = new CommissionFeeCalculator(
    $configurationProvider->getConfig(),
    new CurrencyConverter(new UrlExchangeRateProvider()),
    new CommissionFeeStrategyFactory($configurationProvider->getConfig())
);

$command = new CalculateCommissionFeesCommand(
    new ConsoleLogger(new ConsoleOutput()),
    new CsvOperationsDataProvider(),
    $calculator,
    new ConsoleCommissionFeesWriter()
);

$application->add($command);

$application->setDefaultCommand($command->getName(), true);
$application->run();
