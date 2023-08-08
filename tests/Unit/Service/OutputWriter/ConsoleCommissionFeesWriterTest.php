<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\OutputWriter;

use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Ypppa\CommissionFees\Service\OutputWriter\ConsoleCommissionFeesWriter;

/**
 * @codeCoverageIgnore
 */
class ConsoleCommissionFeesWriterTest extends TestCase
{
    private OutputInterface $output;
    private ConsoleCommissionFeesWriter $outputWriter;

    public function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->outputWriter = new ConsoleCommissionFeesWriter($this->output);
    }

    /**
     * @dataProvider writeDataProvider
     *
     * @param Money  $commissionFee
     * @param string $expectedResult
     *
     * @return void
     */
    public function testWrite(Money $commissionFee, string $expectedResult): void
    {
        $this->outputWriter->write($commissionFee);
        $this->assertEquals($expectedResult . chr(10), $this->output->fetch());
    }

    public function writeDataProvider(): array
    {
        return [
            [
                'commissionFee' => new Money('0.60', 'EUR'),
                'expectedResult' => '0.60',
            ],
            [
                'commissionFee' => new Money('3.00', 'EUR'),
                'expectedResult' => '3.00',
            ],
            [
                'commissionFee' => new Money('0.00', 'EUR'),
                'expectedResult' => '0.00',
            ],
            [
                'commissionFee' => new Money('0.06', 'EUR'),
                'expectedResult' => '0.06',
            ],
            [
                'commissionFee' => new Money('1.50', 'EUR'),
                'expectedResult' => '1.50',
            ],
            [
                'commissionFee' => new Money('0', 'JPY'),
                'expectedResult' => '0',
            ],
            [
                'commissionFee' => new Money('0.70', 'EUR'),
                'expectedResult' => '0.70',
            ],
            [
                'commissionFee' => new Money('0.30', 'USD'),
                'expectedResult' => '0.30',
            ],
            [
                'commissionFee' => new Money('0.30', 'EUR'),
                'expectedResult' => '0.30',
            ],
            [
                'commissionFee' => new Money('3.00', 'EUR'),
                'expectedResult' => '3.00',
            ],
            [
                'commissionFee' => new Money('0.00', 'EUR'),
                'expectedResult' => '0.00',
            ],
            [
                'commissionFee' => new Money('0.00', 'EUR'),
                'expectedResult' => '0.00',
            ],
            [
                'commissionFee' => new Money('8612', 'JPY'),
                'expectedResult' => '8612',
            ],
        ];
    }
}
