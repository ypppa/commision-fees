<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Normalizer;

use Paysera\Component\Normalization\CoreDenormalizer;

interface DenormalizerFactoryInterface
{
    public function createConfigDenormalizer(): CoreDenormalizer;
}
