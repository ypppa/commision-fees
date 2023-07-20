<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\Registry\GroupedNormalizerRegistryProvider;

class DenormalizerFactory
{
    public function createDenormalizer(): CoreDenormalizer
    {
        $provider = new GroupedNormalizerRegistryProvider();
        $provider->addTypeAwareNormalizer(new ConfigNormalizer());
        $provider->addTypeAwareNormalizer(new MoneyNormalizer());
        $provider->addTypeAwareNormalizer(new OperationNormalizer());
        $provider->addTypeAwareNormalizer(new ExchangeRatesNormalizer());

        return new CoreDenormalizer($provider);
    }
}
