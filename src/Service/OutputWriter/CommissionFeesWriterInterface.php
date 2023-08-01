<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\OutputWriter;

use Evp\Component\Money\Money;

interface CommissionFeesWriterInterface
{
    public function write(Money $commissionFee): void;
}
