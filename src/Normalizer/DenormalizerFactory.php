<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\Registry\GroupedNormalizerRegistryProvider;

class DenormalizerFactory
{
    public static function createDenormalizer(): CoreDenormalizer
    {
        $provider = new GroupedNormalizerRegistryProvider();
        $provider->addTypeAwareNormalizer(new MixedOperationDtoNormalizer(), 'mixed');
        $provider->addTypeAwareNormalizer(new ObjectOperationDtoNormalizer(), 'object');
        $provider->addTypeAwareNormalizer(new ObjectCommissionRuleNormalizer(), 'object');
        $provider->addTypeAwareNormalizer(new ObjectExchangeRatesNormalizer(), 'object');
        $provider->addTypeAwareNormalizer(new MixedConfigNormalizer(), 'mixed');

        return new CoreDenormalizer($provider);
    }
}
