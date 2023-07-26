<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Calculator\Strategy;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;
use Ypppa\CommissionFees\Service\Calculator\Strategy\WithdrawPrivateCommissionFeeStrategy;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;

/**
 * @codeCoverageIgnore
 */
class WithdrawPrivateCommissionFeeStrategyTest extends TestCase
{
    private WithdrawPrivateCommissionFeeStrategy $strategy;
    private MockObject|CurrencyConverter $currencyConverter;

    public function setUp(): void
    {
        $config = $this->createConfiguredMock(
            Config::class,
            [
                'getPrivateFreeWithdrawAmount' => new Money('1000', 'EUR'),
                'getPrivateFreeWithdrawCount' => 2,
                'getPrivateWithdrawCommission' => '0.003',
            ]
        );
        $this->currencyConverter = $this->createMock(CurrencyConverter::class);
        $this->strategy = new WithdrawPrivateCommissionFeeStrategy($config, $this->currencyConverter);
    }

    /**
     * @dataProvider calculateCommissionFeeProvider
     *
     * @param Operation                $operation
     * @param UserCumulativeOperations $cumulativeOperations
     * @param Money                    $expectedResult
     * @param Money[]                  $convertedAmounts
     *
     * @return void
     */
    public function testCalculateCommissionFee(
        Operation $operation,
        UserCumulativeOperations $cumulativeOperations,
        Money $expectedResult,
        array $convertedAmounts
    ): void {
        $this->currencyConverter
            ->expects($this->exactly(count($convertedAmounts)))
            ->method('convert')
            ->willReturnOnConsecutiveCalls(...$convertedAmounts)
        ;

        $commissionFee = $this->strategy->calculateCommissionFee($operation, $cumulativeOperations);
        $this->assertEquals($expectedResult, $commissionFee);
    }

    public function calculateCommissionFeeProvider(): array
    {
        $cumulativeOperations = new UserCumulativeOperations(
            '1',
            'EUR',
            new DateTimeImmutable()
        );
        $cumulativeOperationsMock = $this->createConfiguredMock(
            UserCumulativeOperations::class,
            [
                'getCount' => 2,
                'getAmount' => new Money('2', 'EUR'),
            ]
        );

        return [
            'free withdraw in USD' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1000', 'USD')
                ),
                'cumulativeOperations' => $cumulativeOperations,
                'expectedResult' => new Money('0.00', 'USD'),
                'convertedAmounts' => [new Money('1000', 'EUR')],
            ],
            'free withdraw in JPY' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1000', 'JPY')
                ),
                'cumulativeOperations' => $cumulativeOperations,
                'expectedResult' => new Money('0', 'JPY'),
                'convertedAmounts' => [new Money('1000', 'EUR')],
            ],
            'overflow free amount limit in EUR' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('2000', 'EUR')
                ),
                'cumulativeOperations' => $cumulativeOperations,
                'expectedResult' => new Money('3.00', 'EUR'),
                'convertedAmounts' => [
                    new Money('2000', 'EUR'),
                    new Money('1000', 'EUR'),
                ],
            ],
            'overflow free amount limit in JPY' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('300000', 'JPY')
                ),
                'cumulativeOperations' => $cumulativeOperations,
                'expectedResult' => new Money('897', 'JPY'),
                'convertedAmounts' => [
                    new Money('300000', 'EUR'),
                    new Money('299000', 'JPY'),
                ],
            ],
            'overflow free count limit in USD' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1000', 'USD')
                ),
                'cumulativeOperations' => $cumulativeOperationsMock,
                'expectedResult' => new Money('3.00', 'USD'),
                'convertedAmounts' => [],
            ],
            'overflow free count limit in JPY' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1000', 'JPY')
                ),
                'cumulativeOperations' => $cumulativeOperationsMock,
                'expectedResult' => new Money('3', 'JPY'),
                'convertedAmounts' => [],
            ],
            'overflow free count limit in EUR' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('2000', 'EUR')
                ),
                'cumulativeOperations' => $cumulativeOperationsMock,
                'expectedResult' => new Money('6.00', 'EUR'),
                'convertedAmounts' => [],
            ],
            'overflow count limit small amount in JPY' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1', 'JPY')
                ),
                'cumulativeOperations' => $cumulativeOperationsMock,
                'expectedResult' => new Money('1', 'JPY'),
                'convertedAmounts' => [],
            ],
            'overflow count limit small amount in USD' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1', 'USD')
                ),
                'cumulativeOperations' => $cumulativeOperationsMock,
                'expectedResult' => new Money('0.01', 'USD'),
                'convertedAmounts' => [],
            ],
        ];
    }
}
