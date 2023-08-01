<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\OutputWriter;

use Ypppa\CommissionFees\Model\Operation\Operation;

interface CommissionFeesWriterInterface
{
    public function write(Operation $operation): void;
}
