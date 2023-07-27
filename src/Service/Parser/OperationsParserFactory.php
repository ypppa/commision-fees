<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Paysera\Component\Normalization\CoreDenormalizer;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;

class OperationsParserFactory
{
    public const CSV_FILE_FORMAT = 'csv';
    public const JSON_FILE_FORMAT = 'json';

    private CoreDenormalizer $mixedDenormalizer;
    private CoreDenormalizer $objectDenormalizer;

    public function __construct(CoreDenormalizer $mixedDenormalizer, CoreDenormalizer $objectDenormalizer)
    {
        $this->mixedDenormalizer = $mixedDenormalizer;
        $this->objectDenormalizer = $objectDenormalizer;
    }

    /**
     * @param string $filePath
     * @param string $format
     *
     * @return OperationsParserInterface
     * @throws InvalidFileFormatException
     */
    public function getParser(string $filePath, string $format): OperationsParserInterface
    {
        switch ($format) {
            case self::CSV_FILE_FORMAT:
                return new CsvOperationsParser($this->mixedDenormalizer, $filePath);
            case self::JSON_FILE_FORMAT:
                return new JsonOperationsParser($this->objectDenormalizer, $filePath);
            default:
                throw new InvalidFileFormatException(null);
        }
    }
}
