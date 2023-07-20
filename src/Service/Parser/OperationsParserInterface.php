<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Ypppa\CommissionFees\Model\Operation\OperationCollection;

interface OperationsParserInterface
{
    public function parse(): OperationCollection;
}
