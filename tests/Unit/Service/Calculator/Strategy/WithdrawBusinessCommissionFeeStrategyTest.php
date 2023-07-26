<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Calculator\Strategy;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\Calculator\Strategy\WithdrawBusinessCommissionFeeStrategy;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;

/**
 * @codeCoverageIgnore
 */
class WithdrawBusinessCommissionFeeStrategyTest extends TestCase
{
    private WithdrawBusinessCommissionFeeStrategy $strategy;

    public function setUp(): void
    {
        $config = $this->createConfiguredMock(
            Config::class,
            ['getBusinessWithdrawCommission' => '0.005']
        );
        $currencyConverter = $this->createMock(CurrencyConverter::class);
        $this->strategy = new WithdrawBusinessCommissionFeeStrategy($config, $currencyConverter);
    }

    /**
     * @dataProvider calculateCommissionFeeProvider
     *
     * @param Operation $operation
     * @param Money     $expectedResult
     *
     * @return void
     */
    public function testCalculateCommissionFee(Operation $operation, Money $expectedResult): void
    {
        $commissionFee = $this->strategy->calculateCommissionFee($operation, null);
        $this->assertEquals($expectedResult, $commissionFee);
    }

    public function calculateCommissionFeeProvider(): array
    {
        return [
            'large amount in USD' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('100', 'USD')
                ),
                'expectedResult' => new Money('0.50', 'USD'),
            ],
            'large amount in EUR' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1000', 'EUR')
                ),
                'expectedResult' => new Money('5.00', 'EUR'),
            ],
            'small amount in EUR' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1', 'EUR')
                ),
                'expectedResult' => new Money('0.01', 'EUR'),
            ],
            'zero amount in EUR' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('0', 'EUR')
                ),
                'expectedResult' => new Money('0.00', 'EUR'),
            ],
            'large amount in JPY' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('10000', 'JPY')
                ),
                'expectedResult' => new Money('50', 'JPY'),
            ],
            'small amount in JPY' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1', 'JPY')
                ),
                'expectedResult' => new Money('1', 'JPY'),
            ],
            'zero amount in JPY' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('0', 'JPY')
                ),
                'expectedResult' => new Money('0', 'JPY'),
            ],
        ];
    }
}
