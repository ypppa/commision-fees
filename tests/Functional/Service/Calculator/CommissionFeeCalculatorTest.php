<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional\Service\Calculator;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRate;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRates;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Operation\OperationCollection;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\Calculator\Strategy\CommissionFeeStrategyFactory;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\MockExchangeRateProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;

/**
 * @codeCoverageIgnore
 */
class CommissionFeeCalculatorTest extends TestCase
{
    private CommissionFeeCalculator $calculator;

    public function setUp(): void
    {
        $config = (new Config())
            ->setBaseCurrency('EUR')
            ->setDepositCommission('0.0003')
            ->setPrivateFreeWithdrawAmount(new Money('1000', 'EUR'))
            ->setPrivateFreeWithdrawCount(4)
            ->setPrivateWithdrawCommission('0.003')
            ->setBusinessWithdrawCommission('0.005')
        ;
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider->expects($this->any())->method('getConfig')->willReturn($config);
        $exchangeRates = (new ExchangeRates())
            ->setBase('EUR')
            ->setDate(new DateTimeImmutable('today'))
            ->addRate(new ExchangeRate('JPY', '129.53'))
            ->addRate(new ExchangeRate('USD', '1.1497'))
        ;
        $currencyConverter = new CurrencyConverter(new MockExchangeRateProvider($exchangeRates), $configurationProvider);
        $this->calculator = new CommissionFeeCalculator(
            $configurationProvider,
            $currencyConverter,
            new CommissionFeeStrategyFactory($configurationProvider, $currencyConverter)
        );
    }

    /**
     * @dataProvider calculateProvider
     *
     * @param OperationCollection $operations
     * @param array               $expectedResult
     *
     * @return void
     * @throws CommissionFeeCalculationFailedException
     */
    public function testCalculate(OperationCollection $operations, array $expectedResult): void
    {
        $calculatedOperations = $this->calculator->calculate($operations);
        $commissionFees = [];
        foreach ($calculatedOperations as $operation) {
            $commissionFees[] = $operation->getCommissionFee()->formatAmount();
        }
        $this->assertEquals($expectedResult, $commissionFees);
    }

    public function testCommissionFeeCurrency(): void
    {
        $calculatedOperations = $this->calculator->calculate($this->getExampleOperations());
        foreach ($calculatedOperations as $operation) {
            $this->assertEquals(
                $operation->getOperationAmount()->getCurrency(),
                $operation->getCommissionFee()->getCurrency()
            );
        }
    }

    private function getExampleOperations(): OperationCollection
    {
        $operationCollection = new OperationCollection();

        $operationCollection
            ->add(
                new Operation(
                    new DateTimeImmutable('2014-12-31'),
                    '4',
                    'private',
                    'withdraw',
                    new Money('1200.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2015-01-01'),
                    '4',
                    'private',
                    'withdraw',
                    new Money('1000.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-01-05'),
                    '4',
                    'private',
                    'withdraw',
                    new Money('1000.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-01-05'),
                    '1',
                    'private',
                    'deposit',
                    new Money('200.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-01-06'),
                    '2',
                    'business',
                    'withdraw',
                    new Money('300.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-01-06'),
                    '1',
                    'private',
                    'withdraw',
                    new Money('30000', 'JPY')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-01-07'),
                    '1',
                    'private',
                    'withdraw',
                    new Money('1000.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-01-07'),
                    '1',
                    'private',
                    'withdraw',
                    new Money('100.00', 'USD')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-01-10'),
                    '1',
                    'private',
                    'withdraw',
                    new Money('100.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-01-10'),
                    '2',
                    'business',
                    'deposit',
                    new Money('10000.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-01-10'),
                    '3',
                    'private',
                    'withdraw',
                    new Money('1000.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-02-15'),
                    '1',
                    'private',
                    'withdraw',
                    new Money('300.00', 'EUR')
                )
            )
            ->add(
                new Operation(
                    new DateTimeImmutable('2016-02-19'),
                    '5',
                    'private',
                    'withdraw',
                    new Money('3000000', 'JPY')
                )
            )
        ;

        return $operationCollection;
    }

    /**
     * @dataProvider calculateWithFailureProvider
     *
     * @param OperationCollection $operations
     *
     * @return void
     */
    public function testCalculateWithFailure(OperationCollection $operations): void
    {
        $this->expectException(CommissionFeeCalculationFailedException::class);
        $this->calculator->calculate($operations);
    }

    public function calculateProvider(): array
    {
        return [
            'example data check' => [
                'operations' => $this->getExampleOperations(),
                'expectedResult' => [
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
                ],
            ],
            'empty data check' => [
                'operations' => new OperationCollection(),
                'expectedResult' => [],
            ],
            'deposit one operation' => [
                'operations' => $this->getDepositOneOperations(),
                'expectedResult' => [
                    '0.30',
                ],
            ],
        ];
    }

    private function getDepositOneOperations(): OperationCollection
    {
        $operationCollection = new OperationCollection();

        $operationCollection->add(
            new Operation(
                new DateTimeImmutable('2016-01-05'),
                '1',
                'private',
                'deposit',
                new Money('1000.00', 'EUR')
            )
        );

        return $operationCollection;
    }

    public function calculateWithFailureProvider(): array
    {
        return [
            'missing exchange rate' => [
                'operations' => $this->getMissingExchangeRateOperations(),
            ],
        ];
    }

    private function getMissingExchangeRateOperations(): OperationCollection
    {
        $operationCollection = new OperationCollection();

        $operationCollection->add(
            new Operation(
                new DateTimeImmutable('2016-01-05'),
                '1',
                'private',
                'withdraw',
                new Money('1000.00', 'CAD')
            )
        );

        return $operationCollection;
    }
}
