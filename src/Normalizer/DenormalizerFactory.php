<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\Registry\GroupedNormalizerRegistryProvider;

class DenormalizerFactory
{
    public static function createMixedConfigDenormalizer(): CoreDenormalizer
    {
        $provider = new GroupedNormalizerRegistryProvider();
        $provider->addTypeAwareNormalizer(new MixedConfigNormalizer());
        $provider->addTypeAwareNormalizer(new MixedMoneyNormalizer());

        return new CoreDenormalizer($provider);
    }

    public static function createObjectExchangeRatesDenormalizer(): CoreDenormalizer
    {
        $provider = new GroupedNormalizerRegistryProvider();
        $provider->addTypeAwareNormalizer(new ObjectExchangeRatesNormalizer());

        return new CoreDenormalizer($provider);
    }

    public static function createMixedOperationDenormalizer(): CoreDenormalizer
    {
        $provider = new GroupedNormalizerRegistryProvider();
        $provider->addTypeAwareNormalizer(new MixedOperationNormalizer());

        return new CoreDenormalizer($provider);
    }

    public static function createObjectOperationDenormalizer(): CoreDenormalizer
    {
        $provider = new GroupedNormalizerRegistryProvider();
        $provider->addTypeAwareNormalizer(new ObjectOperationNormalizer());

        return new CoreDenormalizer($provider);
    }

    public static function createObjectCommissionRuleDenormalizer(): CoreDenormalizer
    {
        $provider = new GroupedNormalizerRegistryProvider();
        $provider->addTypeAwareNormalizer(new ObjectCommissionRuleNormalizer());

        return new CoreDenormalizer($provider);
    }
}
