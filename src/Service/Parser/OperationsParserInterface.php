<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Generator;

interface OperationsParserInterface
{
    public function parse(): Generator;
}
