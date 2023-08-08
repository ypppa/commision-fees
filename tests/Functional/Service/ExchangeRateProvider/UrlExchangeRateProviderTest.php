<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Functional\Service\ExchangeRateProvider;

use Exception;
use Ypppa\CommissionFees\Exception\ExchangeRatesLoadException;
use Ypppa\CommissionFees\Exception\RateNotFoundException;
use Ypppa\CommissionFees\Tests\Functional\AbstractTestCase;

/**
 * @codeCoverageIgnore
 */
class UrlExchangeRateProviderTest extends AbstractTestCase
{
    /**
     * @dataProvider getRateDataProvider
     *
     * @param string $currency
     * @param string $expectedResult
     *
     * @return void
     * @throws Exception
     */
    public function testGetRate(string $currency, string $expectedResult): void
    {
        $this->container->setParameter('exchange_rates.url', 'tests/_data/exchange_rates.json');
        $this->container->compile();
        $exchangeRatesProvider = $this->container->get('ypppa.commission_fees.url_exchange_rate_provider');
        $this->assertEquals($expectedResult, $exchangeRatesProvider->getRate('EUR', $currency));
    }

    public function getRateDataProvider(): array
    {
        return [
            [
                'currency' => 'EUR',
                'expectedResult' => '1',
            ],
            [
                'currency' => 'USD',
                'expectedResult' => '1.1497',
            ],
            [
                'currency' => 'JPY',
                'expectedResult' => '129.53',
            ],
            [
                'currency' => 'GBP',
                'expectedResult' => '0.835342',
            ],
            [
                'currency' => 'CLP',
                'expectedResult' => '946.71533',
            ],
        ];
    }

    /**
     * @dataProvider exceptionDataProvider
     *
     * @param string $filePath
     * @param string $exceptionClass
     *
     * @return void
     * @throws Exception
     */
    public function testException(string $filePath, string $exceptionClass): void
    {
        $this->container->setParameter('exchange_rates.url', $filePath);
        $this->container->compile();
        $exchangeRatesProvider = $this->container->get('ypppa.commission_fees.url_exchange_rate_provider');
        $this->expectException($exceptionClass);
        $exchangeRatesProvider->getRate('EUR', 'CAD');
    }

    public function exceptionDataProvider(): array
    {
        return [
            [
                'filePath' => 'tests/_data/exchange_rates_exception.json',
                'exceptionClass' => ExchangeRatesLoadException::class,
            ],
            [
                'filePath' => 'tests/_data/exchange_rates.json',
                'exceptionClass' => RateNotFoundException::class,
            ],
        ];
    }
}
