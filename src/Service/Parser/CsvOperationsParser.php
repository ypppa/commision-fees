<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Evp\Component\Money\MoneyException;
use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;
use Ypppa\CommissionFees\Exception\ValidationException;
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

    public function __construct(
        CoreDenormalizer $denormalizer,
        ValidatorInterface $validator,
        string $filePath
    ) {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->filePath = $filePath;
    }

    /**
     * @throws Throwable
     */
    public function parse(): OperationCollection
    {
        $operations = new OperationCollection();

        try {
            $file = fopen($this->filePath, 'r');
        } catch (Throwable $exception) {
            throw new CommissionFeeCalculationFailedException('Failed to open file', null, $exception);
        }

        while (($row = fgetcsv($file)) !== false) {
            try {
                $operationData = array_combine(self::COLUMN_NAMES, $row);
            } catch (Throwable $exception) {
                throw new InvalidFileFormatException($exception);
            }

            try {
                $operation = $this->denormalizer->denormalize($operationData, Operation::class);

                $violations = $this->validator->validate($operation);
                if ($violations->count() > 0) {
                    throw new ValidationFailedException($operation, $violations);
                }

                $operations->add($operation);
            } catch (InvalidDataException $invalidDataException) {
                throw new InvalidFileFormatException($invalidDataException);
            } catch (ValidationFailedException $validationException) {
                throw new ValidationException($validationException);
            } catch (MoneyException $moneyException) {
                if (str_contains($moneyException->getMessage(), 'Unsupported currency')) {
                    throw new UnsupportedCurrencyException($moneyException->getMessage(), $moneyException);
                }
                throw new CommissionFeeCalculationFailedException(null, null, $moneyException);
            } catch (Throwable $exception) {
                throw new CommissionFeeCalculationFailedException(null, null, $exception);
            }
        }

        fclose($file);

        return $operations;
    }
}
