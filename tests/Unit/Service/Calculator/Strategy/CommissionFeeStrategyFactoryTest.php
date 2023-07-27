<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Calculator\Strategy;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\Calculator\Strategy\CommissionFeeStrategyFactory;
use Ypppa\CommissionFees\Service\Calculator\Strategy\DepositCommissionFeeStrategy;
use Ypppa\CommissionFees\Service\Calculator\Strategy\WithdrawBusinessCommissionFeeStrategy;
use Ypppa\CommissionFees\Service\Calculator\Strategy\WithdrawPrivateCommissionFeeStrategy;
use Ypppa\CommissionFees\Service\Calculator\Strategy\ZeroCommissionFeeStrategy;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;

/**
 * @codeCoverageIgnore
 */
class CommissionFeeStrategyFactoryTest extends TestCase
{
    private CommissionFeeStrategyFactory $commissionFeeStrategyFactory;

    public function setUp(): void
    {
        $this->commissionFeeStrategyFactory = new CommissionFeeStrategyFactory(
            $this->createMock(ConfigurationProviderInterface::class),
            $this->createMock(CurrencyConverter::class),
        );
    }

    /**
     * @dataProvider getStrategyProvider
     *
     * @param Operation $operation
     * @param string    $expectedResult
     *
     * @return void
     */
    public function testGetStrategy(Operation $operation, string $expectedResult): void
    {
        $strategy = $this->commissionFeeStrategyFactory->getStrategy($operation);
        $this->assertInstanceOf($expectedResult, $strategy);
    }

    public function getStrategyProvider(): array
    {
        return [
            'DepositCommissionFeeStrategy private user' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money()
                ),
                'expectedResult' => DepositCommissionFeeStrategy::class,
            ],
            'DepositCommissionFeeStrategy business user' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money()
                ),
                'expectedResult' => DepositCommissionFeeStrategy::class,
            ],
            'DepositCommissionFeeStrategy some different user type' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    'some_different_user_type',
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money()
                ),
                'expectedResult' => DepositCommissionFeeStrategy::class,
            ],
            'WithdrawBusinessCommissionFeeStrategy business user' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_BUSINESS,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money()
                ),
                'expectedResult' => WithdrawBusinessCommissionFeeStrategy::class,
            ],
            'WithdrawPrivateCommissionFeeStrategy business user' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money()
                ),
                'expectedResult' => WithdrawPrivateCommissionFeeStrategy::class,
            ],
            'default strategy' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1',
                    'some_different_user_type',
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money()
                ),
                'expectedResult' => ZeroCommissionFeeStrategy::class,
            ],
        ];
    }
}
