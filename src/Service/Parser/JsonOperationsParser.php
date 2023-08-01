<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Evp\Component\Money\MoneyException;
use Generator;
use Paysera\Component\Normalization\CoreDenormalizer;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Validator\MetadataValidatorFactory;

class JsonOperationsParser implements OperationsParserInterface
{
    private string $filePath;
    private CoreDenormalizer $denormalizer;

    public function __construct(CoreDenormalizer $denormalizer, string $filePath)
    {
        $this->filePath = $filePath;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @return Generator
     * @throws CommissionFeeCalculationFailedException
     * @throws InvalidFileFormatException
     * @throws UnsupportedCurrencyException
     */
    public function parse(): Generator
    {
        $validator = MetadataValidatorFactory::createValidator();
        $json = file_get_contents($this->filePath);
        $objectArray = json_decode($json);
        foreach ($objectArray as $object) {
            try {
                $operation = $this->denormalizer->denormalize($object, Operation::class);

                $violations = $validator->validate($operation);
                if ($violations->count() > 0) {
                    throw new ValidationFailedException($operation, $violations);
                }

                yield $operation;
            } catch (ValidationFailedException $invalidDataException) {
                throw new InvalidFileFormatException($invalidDataException);
            } catch (MoneyException $moneyException) {
                if (str_contains($moneyException->getMessage(), 'Unsupported currency')) {
                    throw new UnsupportedCurrencyException($moneyException->getMessage(), $moneyException);
                }
                throw new CommissionFeeCalculationFailedException('', null, $moneyException);
            } catch (Throwable $exception) {
                throw new CommissionFeeCalculationFailedException('', null, $exception);
            }
        }
    }
}
