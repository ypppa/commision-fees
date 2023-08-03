<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Reader;

use Generator;
use Ypppa\CommissionFees\Exception\ReaderException;

interface ReaderInterface
{
    /**
     * @param string $filePath
     *
     * @return Generator
     * @throws ReaderException
     */
    public function read(string $filePath): Generator;
}
