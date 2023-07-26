<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Calculator\Strategy;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\Calculator\Strategy\DepositCommissionFeeStrategy;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;

/**
 * @codeCoverageIgnore
 */
class DepositCommissionFeeStrategyTest extends TestCase
{
    private DepositCommissionFeeStrategy $strategy;

    public function setUp(): void
    {
        $config = $this->createConfiguredMock(Config::class, ['getDepositCommission' => '0.0003']);
        $currencyConverter = $this->createMock(CurrencyConverter::class);
        $this->strategy = new DepositCommissionFeeStrategy($config, $currencyConverter);
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
            'private user in USD large amount' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('1000', 'USD')
                ),
                'expectedResult' => new Money('0.30', 'USD'),
            ],
            'business user in EUR large amount' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('1000', 'EUR')
                ),
                'expectedResult' => new Money('0.30', 'EUR'),
            ],
            'business user in EUR small amount' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('1', 'EUR')
                ),
                'expectedResult' => new Money('0.01', 'EUR'),
            ],
            'business user in EUR zero amount' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('0', 'EUR')
                ),
                'expectedResult' => new Money('0.00', 'EUR'),
            ],
            'private user in JPY large amount' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('10000', 'JPY')
                ),
                'expectedResult' => new Money('3', 'JPY'),
            ],
            'private user in JPY small amount' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('1', 'JPY')
                ),
                'expectedResult' => new  Money('1', 'JPY'),
            ],
            'private user in JPY zero amount' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('0', 'JPY')
                ),
                'expectedResult' => new Money('0', 'JPY'),
            ],
        ];
    }
}
