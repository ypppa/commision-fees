<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional\Command;

use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Ypppa\CommissionFees\Command\CalculateCommissionFeesCommand;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;
use Ypppa\CommissionFees\Service\OutputWriter\ConsoleCommissionFeesWriter;
use Ypppa\CommissionFees\Tests\Functional\AbstractTestCase;

/**
 * @codeCoverageIgnore
 */
class CalculateCommissionFeesCommandTest extends AbstractTestCase
{
    private CalculateCommissionFeesCommand $command;
    private BufferedOutput $output;

    public function setUp(): void
    {
        parent::setUp();

        $logger = new NullLogger();
        $this->output = new BufferedOutput();
        $writer = new ConsoleCommissionFeesWriter($this->output);
        $this->container->setParameter('exchange_rates.url', 'tests/_data/exchange_rates.json');
        $this->container->set('ypppa.commission_fees.logger', $logger);
        $this->container->set('ypppa.commission_fees.console_commission_fees_writer', $writer);

        $this->container->compile();

        $this->command = $this->container->get('ypppa.commission_fees.command.calculate_commission_fees');
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
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'file_path' => $filePath,
            'format' => 'csv',
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
                    '0.70',
                    '0.30',
                    '0.30',
                    '3.00',
                    '0.00',
                    '0.00',
                    '8612',
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
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'file_path' => $filePath,
            'format' => 'csv',
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
