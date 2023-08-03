<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Reader;

use Generator;
use JsonMachine\Items;
use Throwable;
use Ypppa\CommissionFees\Exception\ReaderException;

class JsonReader implements ReaderInterface
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
            $items = Items::fromFile($filePath);
            foreach ($items as $item) {
                yield $item;
            }
        } catch (Throwable $exception) {
            throw new ReaderException('Failed to open file', 0, $exception);
        }
    }
}
