<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Paysera\Component\Normalization\CoreDenormalizer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Operation\OperationCollection;

class CsvOperationsParser implements OperationsParserInterface
{
    private const COLUMN_NAMES = [
        'date',
        'user_id',
        'user_type',
        'operation_type',
        'operation_amount',
        'operation_currency',
    ];

    private string $filePath;
    private CoreDenormalizer $denormalizer;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    public function __construct(
        CoreDenormalizer $denormalizer,
        ValidatorInterface $validator,
        string $filePath,
        LoggerInterface $logger
    ) {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->filePath = $filePath;
        $this->logger = $logger;
    }

    public function parse(): OperationCollection
    {
        $operations = new OperationCollection();

        $file = fopen($this->filePath, 'r');

        while (($row = fgetcsv($file)) !== false) {
            try {
                $operationData = array_combine(self::COLUMN_NAMES, $row);
                $operation = $this->denormalizer->denormalize($operationData, Operation::class);

                $violations = $this->validator->validate($operation);
                if ($violations->count() > 0) {
                    throw new ValidationFailedException($operation, $violations);
                }

                $operations->add($operation);
            } catch (Throwable $exception) {
                $this->logger->error($exception);
            }
        }

        fclose($file);

        return $operations;
    }
}
