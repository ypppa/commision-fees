<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Generator;
use Paysera\Component\Normalization\CoreDenormalizer;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\ReaderException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\Reader\ReaderInterface;

class OperationsParser implements ParserInterface
{
    private ReaderInterface $reader;
    private CoreDenormalizer $denormalizer;

    public function __construct(ReaderInterface $reader, CoreDenormalizer $denormalizer)
    {
        $this->reader = $reader;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param string $filePath
     *
     * @return Generator
     * @throws CommissionFeeCalculationFailedException
     */
    public function parse(string $filePath): Generator
    {
        try {
            foreach ($this->reader->read($filePath) as $input) {
                $operation = $this->denormalizer->denormalize($input, Operation::class);

                yield $operation;
            }
        } catch (ReaderException $exception) {
            throw new CommissionFeeCalculationFailedException('', null, $exception);
        }
    }
}
