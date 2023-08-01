<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;

interface CommissionRulesProviderInterface
{
    public function getRule(Operation $operation): CommissionFeeRule;
}
