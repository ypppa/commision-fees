<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Ypppa\CommissionFees\Exception\InvalidFileFormatException;
use Ypppa\CommissionFees\Model\Operation\OperationCollection;
use Ypppa\CommissionFees\Service\Parser\OperationsParserFactory;
use Ypppa\CommissionFees\Service\Parser\OperationsParserInterface;

class OperationsDataProvider implements OperationsDataProviderInterface
{
    private OperationsParserFactory $parserFactory;
    private ?OperationsParserInterface $parser;
    private ?OperationCollection $operations;

    public function __construct(OperationsParserFactory $parserFactory)
    {
        $this->parserFactory = $parserFactory;
        $this->parser = null;
        $this->operations = null;
    }

    /**
     * @param string $filePath
     * @param string $format
     *
     * @return OperationCollection
     * @throws InvalidFileFormatException
     */
    public function getOperations(string $filePath, string $format): OperationCollection
    {
        if ($this->parser === null) {
            $this->parser = $this->parserFactory->getParser($filePath, $format);
        }
        if ($this->operations === null) {
            $this->load();
        }

        return $this->operations;
    }

    private function load(): void
    {
        $this->operations = $this->parser->parse();
    }
}
