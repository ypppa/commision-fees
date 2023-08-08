<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional\Service\CurrencyConverter;

use Evp\Component\Money\Money;
use Ypppa\CommissionFees\Exception\RateNotFoundException;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;
use Ypppa\CommissionFees\Tests\Functional\AbstractTestCase;

/**
 * @codeCoverageIgnore
 */
class CurrencyConverterTest extends AbstractTestCase
{
    private CurrencyConverter $currencyConverter;

    public function setUp(): void
    {
        parent::setUp();
        $config = $this->createConfiguredMock(
            Config::class,
            ['getBaseCurrency' => 'EUR']
        );
        $this->container->setParameter('exchange_rates.url', 'tests/_data/exchange_rates.json');
        $this->container->compile();
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider->expects($this->any())->method('getConfig')->willReturn($config);
        $this->currencyConverter = new CurrencyConverter(
            $this->container->get('ypppa.commission_fees.url_exchange_rate_provider'),
            $configurationProvider);
    }

    /**
     * @dataProvider convertDataProvider
     *
     * @param Money  $amount
     * @param string $currency
     * @param Money  $expectedResult
     *
     * @return void
     */
    public function testConvert(Money $amount, string $currency, Money $expectedResult): void
    {
        $convertedAmount = $this->currencyConverter->convert($amount, $currency);
        $this->assertEquals($expectedResult, $convertedAmount);
    }

    public function testExceptions(): void
    {
        $this->expectException(RateNotFoundException::class);
        $this->currencyConverter->convert(new Money('100', 'EUR'), 'CAD');
    }

    public function convertDataProvider(): array
    {
        return [
            'equal currencies non zero amount' => [
                'amount' => new Money('100', 'EUR'),
                'currency' => 'EUR',
                'expectedResult' => new Money('100', 'EUR'),
            ],
            'equal currencies zero amount' => [
                'amount' => new Money('0', 'EUR'),
                'currency' => 'EUR',
                'expectedResult' => new Money('0', 'EUR'),
            ],
            'from base currency zero amount' => [
                'amount' => new Money('0', 'EUR'),
                'currency' => 'USD',
                'expectedResult' => new Money('0.000000', 'USD'),
            ],
            'to base currency zero amount' => [
                'amount' => new Money('0', 'USD'),
                'currency' => 'EUR',
                'expectedResult' => new Money('0.000000', 'EUR'),
            ],
            'cross rate conversion zero amount' => [
                'amount' => new Money('0', 'USD'),
                'currency' => 'JPY',
                'expectedResult' => new Money('0.000000', 'JPY'),
            ],
            'to base currency non zero amount' => [
                'amount' => new Money('100', 'USD'),
                'currency' => 'EUR',
                'expectedResult' => new Money('86.979212', 'EUR'),
            ],
            'from base currency non zero amount' => [
                'amount' => new Money('100', 'EUR'),
                'currency' => 'USD',
                'expectedResult' => new Money('114.970000', 'USD'),
            ],
            'cross rate conversion non zero amount' => [
                'amount' => new Money('100', 'USD'),
                'currency' => 'JPY',
                'expectedResult' => new Money('11266.417326', 'JPY'),
            ],
            'backward cross rate conversion non zero amount' => [
                'amount' => new Money('1000', 'JPY'),
                'currency' => 'USD',
                'expectedResult' => new Money('8.875936', 'USD'),
            ],
        ];
    }
}
