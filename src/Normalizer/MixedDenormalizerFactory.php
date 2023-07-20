<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\Registry\GroupedNormalizerRegistryProvider;

class MixedDenormalizerFactory implements DenormalizerFactoryInterface
{
    public function createConfigDenormalizer(): CoreDenormalizer
    {
        $provider = new GroupedNormalizerRegistryProvider();
        $provider->addTypeAwareNormalizer(new ConfigNormalizer());
        $provider->addTypeAwareNormalizer(new MoneyNormalizer());

        return new CoreDenormalizer($provider);
    }

    public function createOperationDenormalizer(): CoreDenormalizer
    {
        $provider = new GroupedNormalizerRegistryProvider();
        $provider->addTypeAwareNormalizer(new OperationNormalizer());

        return new CoreDenormalizer($provider);
    }
}
