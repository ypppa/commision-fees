<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\OutputWriter;

use Ypppa\CommissionFees\Model\Operation\OperationCollection;

interface CommissionFeesWriterInterface
{
    public function write(OperationCollection $operations): void;
}
