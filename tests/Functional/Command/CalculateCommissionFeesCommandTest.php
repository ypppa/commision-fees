<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional\Command;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Ypppa\CommissionFees\Command\CalculateCommissionFeesCommand;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;
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
use Ypppa\CommissionFees\Service\Parser\OperationsParserFactory;
use Ypppa\CommissionFees\Validator\MetadataValidatorFactory;

class CalculateCommissionFeesCommandTest extends TestCase
{
    private CommissionFeeCalculator $calculator;
    private CalculateCommissionFeesCommand $commandWithSuccess;
    private BufferedOutput $output;

    public function setUp(): void
    {
        $logger = new ConsoleLogger(new ConsoleOutput());
        $configurationProvider = new YamlConfigurationProvider(
            MetadataValidatorFactory::createValidator(),
            DenormalizerFactory::createMixedConfigDenormalizer(),
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
        $this->calculator = new CommissionFeeCalculator(
            $configurationProvider,
            $currencyConverter,
            new CommissionFeeStrategyFactory($configurationProvider, $currencyConverter)
        );
        $operationsDataProvider = new OperationsDataProvider(
            new OperationsParserFactory(
                DenormalizerFactory::createMixedOperationDenormalizer(),
                DenormalizerFactory::createObjectOperationDenormalizer()
            )
        );

        $this->output = new BufferedOutput();
        $this->commandWithSuccess = new CalculateCommissionFeesCommand(
            $logger,
            $operationsDataProvider,
            $this->calculator,
            new ConsoleCommissionFeesWriter($this->output)
        );
    }

    public function testCommandHappyPath(): void
    {
        $commandTester = new CommandTester($this->commandWithSuccess);
        $commandTester->execute([
            'file_path' => 'tests/_data/operations.csv',
            'format' => OperationsParserFactory::CSV_FILE_FORMAT,
        ]);
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

    /**
     * @dataProvider exceptionProvider
     *
     * @param string $filePath
     * @param int    $expectedResultCode
     * @param string $expectedMessage
     *
     * @return void
     */
    public function testExceptions(string $filePath, int $expectedResultCode, string $expectedMessage): void
    {
        $command = new CalculateCommissionFeesCommand(
            new NullLogger(),
            new OperationsDataProvider(
                new OperationsParserFactory(
                    DenormalizerFactory::createMixedOperationDenormalizer(),
                    DenormalizerFactory::createObjectOperationDenormalizer()
                )
            ),
            $this->calculator,
            new ConsoleCommissionFeesWriter($this->output)
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file_path' => $filePath,
            'format' => OperationsParserFactory::CSV_FILE_FORMAT,
        ]);
        $this->assertEquals($expectedResultCode, $commandTester->getStatusCode());
        $this->assertStringContainsString($expectedMessage, $commandTester->getDisplay());
    }

    public function exceptionProvider(): array
    {
        return [
            'Unsupported currency' => [
                'filePath' => 'tests/_data/operations_unsupported_currency.csv',
                'expectedResultCode' => UnsupportedCurrencyException::UNSUPPORTED_CURRENCY_ERROR_CODE,
                'expectedMessage' => 'Unsupported currency',
            ],
            'Invalid file format' => [
                'filePath' => 'tests/_data/operations_invalid_format.csv',
                'expectedResultCode' => InvalidFileFormatException::INVALID_FILE_FORMAT_ERROR_CODE,
                'expectedMessage' => 'Invalid file format',
            ],
            'Base exception' => [
                'filePath' => 'tests/_data/operations_unexpected_error.csv',
                'expectedResultCode' => CommissionFeeCalculationFailedException::UNEXPECTED_ERROR_CODE,
                'expectedMessage' => 'Commission fees calculation failed',
            ],
        ];
    }
}
