<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional\Service\Calculator;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRate;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRates;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\Calculator\CommissionFeeCalculator;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\MockExchangeRateProvider;
use Ypppa\CommissionFees\Tests\Functional\AbstractTestCase;

/**
 * @codeCoverageIgnore
 */
class CommissionFeeCalculatorTest extends AbstractTestCase
{
    private CommissionFeeCalculator $calculator;

    public function setUp(): void
    {
        parent::setUp();
        $exchangeRates = (new ExchangeRates())
            ->setBase('EUR')
            ->setDate(new DateTimeImmutable('today'))
            ->addRate(new ExchangeRate('JPY', '129.53'))
            ->addRate(new ExchangeRate('USD', '1.1497'))
        ;
        $this->container->set(
            'ypppa.commission_fees.url_exchange_rate_provider',
            new MockExchangeRateProvider($exchangeRates)
        );
        $this->container->compile();
        $this->calculator = $this->container->get('ypppa.commission_fees.commission_fee_calculator');
    }

    /**
     * @dataProvider calculateProvider
     *
     * @param Operation[] $operations
     * @param array       $expectedResult
     *
     * @return void
     * @throws CommissionFeeCalculationFailedException
     */
    public function testCalculate(array $operations, array $expectedResult): void
    {
        $commissionFees = [];
        foreach ($operations as $operation) {
            $commissionFees[] = $this->calculator->calculate($operation)->formatAmount();
        }

        $this->assertEquals($expectedResult, $commissionFees);
    }

    /**
     * @throws CommissionFeeCalculationFailedException
     */
    public function testCommissionFeeCurrency(): void
    {
        foreach ($this->getExampleOperations() as $operation) {
            $commissionFee = $this->calculator->calculate($operation);
            $this->assertEquals(
                $operation->getOperationAmount()->getCurrency(),
                $commissionFee->getCurrency()
            );
        }
    }

    /**
     * @return Operation[]
     */
    private function getExampleOperations(): array
    {
        return [
            new Operation(
                new DateTimeImmutable('2014-12-31'),
                '4',
                'private',
                'withdraw',
                new Money('1200.00', 'EUR')
            ),

            new Operation(
                new DateTimeImmutable('2015-01-01'),
                '4',
                'private',
                'withdraw',
                new Money('1000.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2016-01-05'),
                '4',
                'private',
                'withdraw',
                new Money('1000.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2016-01-05'),
                '1',
                'private',
                'deposit',
                new Money('200.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2016-01-06'),
                '2',
                'business',
                'withdraw',
                new Money('300.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2016-01-06'),
                '1',
                'private',
                'withdraw',
                new Money('30000', 'JPY')
            ),
            new Operation(
                new DateTimeImmutable('2016-01-07'),
                '1',
                'private',
                'withdraw',
                new Money('1000.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2016-01-07'),
                '1',
                'private',
                'withdraw',
                new Money('100.00', 'USD')
            ),
            new Operation(
                new DateTimeImmutable('2016-01-10'),
                '1',
                'private',
                'withdraw',
                new Money('100.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2016-01-10'),
                '2',
                'business',
                'deposit',
                new Money('10000.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2016-01-10'),
                '3',
                'private',
                'withdraw',
                new Money('1000.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2016-02-15'),
                '1',
                'private',
                'withdraw',
                new Money('300.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2016-02-19'),
                '5',
                'private',
                'withdraw',
                new Money('3000000', 'JPY')
            ),
        ];
    }

    /**
     * @dataProvider calculateWithFailureProvider
     *
     * @param Operation[] $operations
     *
     * @return void
     */
    public function testCalculateWithFailure(array $operations): void
    {
        $this->expectException(CommissionFeeCalculationFailedException::class);
        foreach ($operations as $operation) {
            $this->calculator->calculate($operation);
        }
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
                'operations' => [],
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

    /**
     * @return Operation[]
     */
    private function getDepositOneOperations(): array
    {
        return [
            new Operation(
                new DateTimeImmutable('2016-01-05'),
                '1',
                'private',
                'deposit',
                new Money('1000.00', 'EUR')
            ),
        ];
    }

    public function calculateWithFailureProvider(): array
    {
        return [
            'missing exchange rate' => [
                'operations' => $this->getMissingExchangeRateOperations(),
            ],
        ];
    }

    /**
     * @return Operation[]
     */
    private function getMissingExchangeRateOperations(): array
    {
        return [
            new Operation(
                new DateTimeImmutable('2016-01-05'),
                '1',
                'private',
                'withdraw',
                new Money('1000.00', 'CAD')
            ),
        ];
    }
}
