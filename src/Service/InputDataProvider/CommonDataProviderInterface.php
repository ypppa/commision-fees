<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

interface CommonDataProviderInterface
{
    public function load(): void;
}
