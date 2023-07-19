<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Ypppa\CommissionFees\Model\Config\Config;

interface ConfigurationProviderInterface
{
    public function getConfig(): Config;
}
