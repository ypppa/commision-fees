<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Reader;

use Generator;
use Throwable;
use Ypppa\CommissionFees\Exception\ReaderException;

class CsvReader implements ReaderInterface
{
    /**
     * @param string $filePath
     *
     * @return Generator
     * @throws ReaderException
     */
    public function read(string $filePath): Generator
    {
        try {
            $file = fopen($filePath, 'r');
        } catch (Throwable $exception) {
            throw new ReaderException('Failed to open file', 0, $exception);
        }
        while (($row = fgetcsv($file)) !== false) {
            yield $row;
        }

        fclose($file);
    }
}
