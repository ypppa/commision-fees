<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use DateTimeImmutable;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\ObjectDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
use Throwable;
use Ypppa\CommissionFees\Exception\DenormalizationException;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRate;
use Ypppa\CommissionFees\Model\ExchangeRate\ExchangeRates;

class ObjectExchangeRatesNormalizer implements ObjectDenormalizerInterface, TypeAwareInterface
{
    public function getType(): string
    {
        return ExchangeRates::class;
    }

    /**
     * @param ObjectWrapper          $input
     * @param DenormalizationContext $context
     *
     * @return ExchangeRates
     * @throws DenormalizationException
     */
    public function denormalize(ObjectWrapper $input, DenormalizationContext $context): ExchangeRates
    {
        try {
            $date = new DateTimeImmutable($input->getRequiredString('date'));


            $exchangeRates = (new ExchangeRates())
                ->setBase($input->getRequiredString('base'))
                ->setDate($date)
            ;

            $ratesObject = $input->getRequiredObject('rates');
            $ratesArray = $ratesObject->getDataAsArray();
            foreach ($ratesArray as $currency => $rate) {
                $exchangeRates->addRate(new ExchangeRate($currency, strval($rate)));
            }

            return $exchangeRates;
        } catch (Throwable $exception) {
            throw new DenormalizationException($exception);
        }
    }
}
