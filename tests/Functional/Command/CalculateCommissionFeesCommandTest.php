<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional\Command;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Ypppa\CommissionFees\Command\CalculateCommissionFeesCommand;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRate;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRates;
use Ypppa\CommissionFees\Normalizer\DenormalizerFactory;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\Calculator\Strategy\CommissionFeeStrategyFactory;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\MockExchangeRateProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\OperationsDataProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\YamlConfigurationProvider;
use Ypppa\CommissionFees\Service\OutputWriter\ConsoleCommissionFeesWriter;
use Ypppa\CommissionFees\Service\Parser\CsvOperationsParser;
use Ypppa\CommissionFees\Validator\MetadataValidatorFactory;

class CalculateCommissionFeesCommandTest extends TestCase
{
    private CalculateCommissionFeesCommand $commandWithSuccess;
    private CalculateCommissionFeesCommand $commandWithFailure;
    private BufferedOutput $output;

    public function setUp(): void
    {
        $logger = new NullLogger();
        $configurationProvider = new YamlConfigurationProvider(
            DenormalizerFactory::createDenormalizer(),
            MetadataValidatorFactory::createValidator(),
            'tests/_data/config.yaml'
        );
        $exchangeRates = (new ExchangeRates())
            ->setBase('EUR')
            ->setDate(new DateTimeImmutable('today'))
            ->addRate(new ExchangeRate('EUR', '1'))
            ->addRate(new ExchangeRate('JPY', '130.869977'))
            ->addRate(new ExchangeRate('USD', '1.129031'))
        ;
        $currencyConverter = new CurrencyConverter(
            new MockExchangeRateProvider($exchangeRates),
            $configurationProvider
        );
        $calculator = new CommissionFeeCalculator(
            $configurationProvider,
            $currencyConverter,
            new CommissionFeeStrategyFactory($configurationProvider, $currencyConverter)
        );
        $operationsDataProvider = new OperationsDataProvider(
            new CsvOperationsParser(
                DenormalizerFactory::createDenormalizer(),
                MetadataValidatorFactory::createValidator(),
                'tests/_data/operations.csv',
                $logger
            )
        );

        $this->output = new BufferedOutput();
        $this->commandWithSuccess = new CalculateCommissionFeesCommand(
            $logger,
            $operationsDataProvider,
            $calculator,
            new ConsoleCommissionFeesWriter($this->output)
        );

        $operationsDataProvider = new OperationsDataProvider(
            new CsvOperationsParser(
                (new DenormalizerFactory())->createDenormalizer(),
                (new MetadataValidatorFactory())->createValidator(),
                'tests/_data/operations_invalid_format.csv',
                $logger
            )
        );

        $this->commandWithFailure = new CalculateCommissionFeesCommand(
            $logger,
            $operationsDataProvider,
            $calculator,
            new ConsoleCommissionFeesWriter($this->output)
        );
    }

    public function testCommandHappyPath(): void
    {
        $commandTester = new CommandTester($this->commandWithSuccess);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        $this->assertEquals(
            implode(chr(10), [
                '0.60',
                '3.00',
                '0.00',
                '0.06',
                '1.50',
                '0',
                '0.69',
                '0.30',
                '0.30',
                '3.00',
                '0.00',
                '0.00',
                '8608',
                '',
            ]),
            $this->output->fetch()
        );
    }

    public function testCommandFailure(): void
    {
        $commandTester = new CommandTester($this->commandWithFailure);
        $commandTester->execute([]);
        $this->assertEquals(255, $commandTester->getStatusCode());
        $this->assertStringContainsString('Unexpected error', $commandTester->getDisplay());
    }
}
