<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Paysera\Component\Normalization\CoreDenormalizer;
use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Service\Reader\CsvReader;
use Ypppa\CommissionFees\Service\Reader\JsonReader;

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
     * @param string $format
     *
     * @return OperationsParser
     * @throws InvalidFileFormatException
     */
    public function getParser(string $format): OperationsParser
    {
        switch ($format) {
            case self::CSV_FILE_FORMAT:
                return new OperationsParser(new CsvReader(), $this->mixedDenormalizer);
            case self::JSON_FILE_FORMAT:
                return new OperationsParser(new JsonReader(), $this->objectDenormalizer);
            default:
                throw new InvalidFileFormatException(null);
        }
    }
}
