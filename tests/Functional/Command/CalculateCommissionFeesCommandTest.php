<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional\Command;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Ypppa\CommissionFees\Command\CalculateCommissionFeesCommand;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRate;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRates;
use Ypppa\CommissionFees\Normalizer\DenormalizerFactory;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\MockExchangeRateProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\CommissionRulesProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\YamlConfigurationProvider;
use Ypppa\CommissionFees\Service\Manager\UserHistoryManager;
use Ypppa\CommissionFees\Service\OutputWriter\ConsoleCommissionFeesWriter;
use Ypppa\CommissionFees\Service\Parser\CommissionRulesParser;
use Ypppa\CommissionFees\Service\Parser\OperationsParserFactory;
use Ypppa\CommissionFees\Service\Reader\JsonReader;
use Ypppa\CommissionFees\Validator\MetadataValidatorFactory;

/**
 * @codeCoverageIgnore
 */
class CalculateCommissionFeesCommandTest extends TestCase
{
    private CommissionFeeCalculator $calculator;
    private CalculateCommissionFeesCommand $commandWithSuccess;
    private BufferedOutput $output;

    public function setUp(): void
    {
        $logger = new NullLogger();
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
        $commissionRulesProvider = new CommissionRulesProvider(
            new CommissionRulesParser(new JsonReader(), DenormalizerFactory::createObjectCommissionRuleDenormalizer()),
            'commission_fee_rules.json'
        );
        $this->calculator = new CommissionFeeCalculator(
            $configurationProvider,
            $currencyConverter,
            $commissionRulesProvider,
            new UserHistoryManager()
        );
        $operationsParserFactory = new OperationsParserFactory(
            DenormalizerFactory::createMixedOperationDenormalizer(),
            DenormalizerFactory::createObjectOperationDenormalizer()
        );

        $this->output = new BufferedOutput();
        $this->commandWithSuccess = new CalculateCommissionFeesCommand(
            $logger,
            $operationsParserFactory,
            $this->calculator,
            new ConsoleCommissionFeesWriter($this->output),
            MetadataValidatorFactory::createValidator()
        );
    }

    /**
     * @dataProvider commandDataProvider
     *
     * @param string $filePath
     * @param string $expectedResult
     *
     * @return void
     */
    public function testCommand(string $filePath, string $expectedResult): void
    {
        $commandTester = new CommandTester($this->commandWithSuccess);
        $commandTester->execute([
            'file_path' => $filePath,
            'format' => OperationsParserFactory::CSV_FILE_FORMAT,
        ]);
        $commandTester->assertCommandIsSuccessful();
        $this->assertEquals($expectedResult, $this->output->fetch());
    }

    public function commandDataProvider(): array
    {
        return [
            'example data happy path' => [
                'filePath' => 'tests/_data/operations.csv',
                'expectedResult' => implode(chr(10), [
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
            ],
            'specific rules' => [
                'filePath' => 'tests/_data/operations_specific_rules.csv',
                'expectedResult' => implode(chr(10), [
                    '0.00',
                    '0.00',
                    '3.00',
                    '0.00',
                    '3.00',
                    '13.00',
                    '0.50',
                    '0.50',
                    '0.00',
                    '0.00',
                    '0.50',
                    '10.00',
                    '0.00',
                    '0.00',
                    '0.00',
                    '0.00',
                    '0.00',
                    '0.00',
                    '0.00',
                    '0.00',
                    '0.00',
                    '0.00',
                    '2.50',
                    '',
                ]),
            ],
            'operations order' => [
                'filePath' => 'tests/_data/operations_order.csv',
                'expectedResult' => implode(chr(10), [
                    '0.60',
                    '0.60',
                    '3.60',
                    '',
                ]),
            ],
        ];
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
            new OperationsParserFactory(
                DenormalizerFactory::createMixedOperationDenormalizer(),
                DenormalizerFactory::createObjectOperationDenormalizer()
            ),
            $this->calculator,
            new ConsoleCommissionFeesWriter($this->output),
            MetadataValidatorFactory::createValidator()
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
