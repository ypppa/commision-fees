<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\CurrencyConverter;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Exception\RateNotFoundException;
use Ypppa\CommissionFees\Model\Config\Config;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRate;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRates;
use Ypppa\CommissionFees\Service\CurrencyConverter\CurrencyConverter;
use Ypppa\CommissionFees\Service\ExchangeRateProvider\MockExchangeRateProvider;
use Ypppa\CommissionFees\Service\InputDataProvider\ConfigurationProviderInterface;

/**
 * @codeCoverageIgnore
 */
class CurrencyConverterTest extends TestCase
{
    private CurrencyConverter $currencyConverter;

    public function setUp(): void
    {
        $config = $this->createConfiguredMock(
            Config::class,
            ['getBaseCurrency' => 'EUR']
        );
        $exchangeRates = (new ExchangeRates())
            ->setBase('EUR')
            ->setDate(new DateTimeImmutable('today'))
            ->addRate(new ExchangeRate('JPY', '129.53'))
            ->addRate(new ExchangeRate('USD', '1.1497'))
        ;
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider->expects($this->any())->method('getConfig')->willReturn($config);
        $this->currencyConverter = new CurrencyConverter(new MockExchangeRateProvider($exchangeRates), $configurationProvider);
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
