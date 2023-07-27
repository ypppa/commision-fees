<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\ExchangeRateProvider;

use Paysera\Component\Normalization\CoreDenormalizer;
use Throwable;
use Ypppa\CommissionFees\Exception\ExchangeRatesLoadException;
use Ypppa\CommissionFees\Exception\RateNotFoundException;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRates;

class UrlExchangeRateProvider implements ExchangeRateProviderInterface
{
    private string $url;
    private ?ExchangeRates $exchangeRates;
    private CoreDenormalizer $denormalizer;

    public function __construct(CoreDenormalizer $denormalizer, string $url)
    {
        $this->url = $url;
        $this->exchangeRates = null;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @return void
     * @throws ExchangeRatesLoadException
     */
    private function load(): void
    {
        try {
            $data = file_get_contents($this->url);
            $this->exchangeRates = $this->denormalizer->denormalize(json_decode($data), ExchangeRates::class);
        } catch (Throwable $exception) {
            throw new ExchangeRatesLoadException($exception);
        }
    }

    /**
     * @param string $base
     * @param string $currency
     *
     * @return string
     * @throws RateNotFoundException|ExchangeRatesLoadException
     */
    public function getRate(string $base, string $currency): string
    {
        if ($this->exchangeRates === null) {
            $this->load();
        }

        foreach ($this->exchangeRates->getRates() as $exchangeRate) {
            if ($exchangeRate->getCurrency() === $currency) {
                return $exchangeRate->getRate();
            }
        }

        throw new RateNotFoundException(null);
    }
}
