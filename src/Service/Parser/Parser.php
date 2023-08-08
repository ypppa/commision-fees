<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Generator;
use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Serializer\Exception\InvalidDataException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Exception\ReaderException;
use Ypppa\CommissionFees\Exception\ValidationException;
use Ypppa\CommissionFees\Service\Reader\ReaderInterface;

class Parser implements ParserInterface
{
    private ReaderInterface $reader;
    private ValidatorInterface $validator;
    private CoreDenormalizer $denormalizer;
    private DenormalizationContext $denormalizationContext;

    public function __construct(
        ReaderInterface $reader,
        ValidatorInterface $validator,
        CoreDenormalizer $denormalizer,
        DenormalizationContext $denormalizationContext
    ) {
        $this->reader = $reader;
        $this->validator = $validator;
        $this->denormalizer = $denormalizer;
        $this->denormalizationContext = $denormalizationContext;
    }

    /**
     * @param string $filePath
     * @param string $className
     *
     * @return Generator
     * @throws ValidationException
     * @throws CommissionFeeCalculationFailedException
     */
    public function parse(string $filePath, string $className): Generator
    {
        try {
            foreach ($this->reader->read($filePath) as $input) {
                $object = $this->denormalizer->denormalize(
                    $input,
                    $className,
                    $this->denormalizationContext
                );
                $violations = $this->validator->validate($object);
                if ($violations->count() > 0) {
                    throw new ValidationException(new ValidationFailedException($object, $violations));
                }
                yield $object;
            }
        } catch (InvalidDataException $exception) {
            throw new InvalidFileFormatException($exception);
        } catch (ReaderException $exception) {
            throw new CommissionFeeCalculationFailedException('', null, $exception);
        }
    }
}
