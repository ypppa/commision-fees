<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Generator;

interface ParserInterface
{
    public function parse(string $filePath): Generator;
}
