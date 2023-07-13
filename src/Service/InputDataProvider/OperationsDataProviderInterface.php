<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Ypppa\CommissionFees\Model\Operation\OperationCollection;

interface OperationsDataProviderInterface
{
    public function getOperations(): OperationCollection;
}
