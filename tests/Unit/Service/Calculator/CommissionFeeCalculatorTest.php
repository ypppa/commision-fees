<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Calculator;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;
use Ypppa\CommissionFees\Model\User\UserCumulativeOperations;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\InputDataProvider\CommissionRulesProviderInterface;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;
use Ypppa\CommissionFees\Service\Manager\UserHistoryManager;

/**
 * @codeCoverageIgnore
 */
class CommissionFeeCalculatorTest extends TestCase
{
    private MockObject|CurrencyConverter $currencyConverter;
    private CommissionRulesProviderInterface|MockObject $commissionRulesProvider;
    private MockObject|UserHistoryManager $userHistoryManager;
    private CommissionFeeCalculator $calculator;

    public function setUp(): void
    {
        $configurationProvider = $this->createConfiguredMock(
            ConfigurationProviderInterface::class,
            [
                'getConfig' => (new Config())->setBaseCurrency('EUR'),
            ]
        );
        $this->currencyConverter = $this->createMock(CurrencyConverter::class);
        $this->commissionRulesProvider = $this->createMock(CommissionRulesProviderInterface::class);
        $this->userHistoryManager = $this->createMock(UserHistoryManager::class);
        $this->calculator = new CommissionFeeCalculator(
            $configurationProvider,
            $this->currencyConverter,
            $this->commissionRulesProvider,
            $this->userHistoryManager
        );
    }

    /**
     * @dataProvider calculateDataProvider
     *
     * @param Operation         $operation
     * @param array             $cumulativeOperationsMockConfig
     * @param CommissionFeeRule $rule
     * @param Money             $expectedResult
     * @param array             $convertedAmounts
     *
     * @return void
     * @throws CommissionFeeCalculationFailedException
     */
    public function testCalculate(
        Operation $operation,
        array $cumulativeOperationsMockConfig,
        CommissionFeeRule $rule,
        Money $expectedResult,
        array $convertedAmounts
    ): void {
        $this->currencyConverter
            ->expects($this->exactly(count($convertedAmounts)))
            ->method('convert')
            ->willReturnOnConsecutiveCalls(...$convertedAmounts)
        ;
        $cumulativeOperationsMock = $this->createConfiguredMock(
            UserCumulativeOperations::class,
            $cumulativeOperationsMockConfig
        );
        $this->userHistoryManager
            ->expects($this->once())
            ->method('get')
            ->willReturn($cumulativeOperationsMock)
        ;
        $this->commissionRulesProvider
            ->expects($this->once())
            ->method('getRule')
            ->willReturn($rule)
        ;

        $commissionFee = $this->calculator->calculate($operation);
        $this->assertEquals($expectedResult->formatAmount(), $commissionFee->formatAmount());

    }

    public function calculateDataProvider(): array
    {
        $ruleUserId11 = (new CommissionFeeRule())
            ->setUserId(['11'])
            ->setFreeOperationsCountLimit(2)
            ->setFreeOperationsAmountLimit(new Money('200', 'EUR'))
            ->setCommission('0.03')
            ->setCommissionFeeMax(new Money('13', 'EUR'))
        ;
        $ruleUserId111 = (new CommissionFeeRule())
            ->setUserId(['111'])
            ->setCommission('0')
            ->setCommissionFeeMin(new Money('0.5', 'EUR'))
        ;
        $ruleUserId1111 = (new CommissionFeeRule())
            ->setUserId(['1111'])
            ->setFreeOperationsCountLimit(10)
            ->setFreeOperationsAmountLimit(new Money('1000', 'EUR'))
            ->setCommission('0.005')
            ->setCommissionFeeMax(new Money('10', 'EUR'))
        ;

        return [
            'userId = 11 and free count and amount limits are not exceeded' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '11',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('50', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 1,
                    'getAmount' => new Money('0', 'EUR'),
                ],
                'rule' => $ruleUserId11,
                'expectedResult' => new Money('0', 'EUR'),
                'convertedAmounts' => [
                    new Money('50', 'EUR'),
                    new Money('13', 'EUR'),
                ],
            ],
            'userId = 11 and free limits exceeded' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '11',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('100', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 2,
                    'getAmount' => new Money('2', 'EUR'),
                ],
                'rule' => $ruleUserId11,
                'expectedResult' => new Money('3', 'EUR'),
                'convertedAmounts' => [
                    new Money('100', 'EUR'),
                    new Money('13', 'EUR'),
                ],
            ],
            'userId = 11 and free limits partly exceeded' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '11',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('110', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 1,
                    'getAmount' => new Money('100', 'EUR'),
                ],
                'rule' => $ruleUserId11,
                'expectedResult' => new Money('0.30', 'EUR'),
                'convertedAmounts' => [
                    new Money('110', 'EUR'),
                    new Money('10', 'EUR'),
                    new Money('13', 'EUR'),
                ],
            ],
            'userId = 11 and max commission fee amount rule' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '11',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1000', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 2,
                    'getAmount' => new Money('100', 'EUR'),
                ],
                'rule' => $ruleUserId11,
                'expectedResult' => new Money('13', 'EUR'),
                'convertedAmounts' => [
                    new Money('1000', 'EUR'),
                    new Money('13', 'EUR'),
                ],
            ],
            'userId = 111 fixed commission fee for small amount' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '111',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 0,
                    'getAmount' => new Money('0', 'EUR'),
                ],
                'rule' => $ruleUserId111,
                'expectedResult' => new Money('0.5', 'EUR'),
                'convertedAmounts' => [
                    new Money('1', 'EUR'),
                    new Money('0.5', 'EUR'),
                ],
            ],
            'userId = 111 fixed commission fee for large amount' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '111',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1000', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 0,
                    'getAmount' => new Money('0', 'EUR'),
                ],
                'rule' => $ruleUserId111,
                'expectedResult' => new Money('0.5', 'EUR'),
                'convertedAmounts' => [
                    new Money('1000', 'EUR'),
                    new Money('0.5', 'EUR'),
                ],
            ],
            'userId = 1111 and free count and amount limits are not exceeded' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1111',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('100', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 9,
                    'getAmount' => new Money('900', 'EUR'),
                ],
                'rule' => $ruleUserId1111,
                'expectedResult' => new Money('0', 'EUR'),
                'convertedAmounts' => [
                    new Money('100', 'EUR'),
                    new Money('10', 'EUR'),
                ],
            ],
            'userId = 1111 and free limits exceeded' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1111',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('1000', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 10,
                    'getAmount' => new Money('900', 'EUR'),
                ],
                'rule' => $ruleUserId1111,
                'expectedResult' => new Money('5', 'EUR'),
                'convertedAmounts' => [
                    new Money('1000', 'EUR'),
                    new Money('10', 'EUR'),
                ],
            ],
            'userId = 1111 and free limits partly exceeded' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1111',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_WITHDRAW,
                    new Money('200', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 9,
                    'getAmount' => new Money('900', 'EUR'),
                ],
                'rule' => $ruleUserId1111,
                'expectedResult' => new Money('0.5', 'EUR'),
                'convertedAmounts' => [
                    new Money('200', 'EUR'),
                    new Money('100', 'EUR'),
                    new Money('10', 'EUR'),
                ],
            ],
            'userId = 1111 and max commission fee amount rule' => [
                'operation' => new Operation(
                    new DateTimeImmutable(),
                    '1111',
                    Operation::USER_TYPE_PRIVATE,
                    Operation::OPERATION_TYPE_DEPOSIT,
                    new Money('20000', 'EUR')
                ),
                'cumulativeOperationsMockConfig' => [
                    'getCount' => 10,
                    'getAmount' => new Money('900', 'EUR'),
                ],
                'rule' => $ruleUserId1111,
                'expectedResult' => new Money('10', 'EUR'),
                'convertedAmounts' => [
                    new Money('20000', 'EUR'),
                    new Money('10', 'EUR'),
                ],
            ],
        ];
    }
}
